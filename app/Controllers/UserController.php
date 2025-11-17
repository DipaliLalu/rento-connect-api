<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $modelName = UserModel::class;
    protected $format    = 'json';
    protected $cacheTtl  = 300; // default 5 minutes

    /**
     * List all users
     */
    public function index()
    {
        $users = $this->model->findAll();
        // // Decode roles
        // foreach ($users as &$user) {
        //     $user['roles'] = json_decode($user['roles'], true);
        // }
        // $user['permission'] = json_decode($user['permission'], true);

        return $this->respond([
            'response'   => true,
            'data' => $users,
        ]);
    }

    /**
     * Show single user
     */
    public function show($id = null)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('User not found');
        }

        $user['roles'] = json_decode($user['roles'], true);
        return $this->respond($user);
    }

    /**
     * Create new user
     */
    public function new()
    {
        $userModel = $this->model;

        // Validation rules
        $validation = \Config\Services::validation();
        $validation->setRules([
            'username' => 'required|min_length[3]',
            'password' => 'required',
            'email'    => 'required|valid_email',
            'id_proof' => 'uploaded[id_proof]|ext_in[id_proof,pdf]', // only pdf
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        // Fetch normal input
        $data = $this->request->getPost();

        // Handle file
        $file = $this->request->getFile('id_proof');
        $filePath = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/id_proof/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $username = $data['username']; // safe name
            $newName = strtolower($username) . '.pdf';

            // If file with same name exists, delete it or rename
            if (file_exists($uploadPath . $newName)) {
                unlink($uploadPath . $newName);
            }

            // Move file
            $file->move($uploadPath, $newName);
            $filePath = 'uploads/id_proof/' . $newName;
        }

        // Decode JSON arrays (if sent from frontend as strings)
        $roles = isset($data['roles']) ? json_decode($data['roles'], true) : '';
        $permission = isset($data['permission']) ? json_decode($data['permission'], true) : '';

        // Prepare data for insert
        $insertData = [
            'username'    => $data['username'],
            'password'    => password_hash($data['password'], PASSWORD_DEFAULT),
            'mobile'      => $data['mobile'] ?? null,
            'email'       => $data['email'],
            'id_proof'    => $filePath,
            'roles'       => json_encode($roles),
            'permission'  => json_encode($permission),
        ];

        // Insert user
        $userId = $userModel->insert($insertData);

        return $this->respondCreated([
            'status' => true,
            'message'  => 'User created successfully',
            'user_id' => $userId,
            'id_proof_path' => $filePath,
        ]);
    }

    /**
     * Update user
     */
    public function update($userId = null)
    {
        $userModel = $this->model;

        if (!$userId) {
            return $this->fail("User ID is required");
        }

        $user = $userModel->find($userId);
        if (!$user) {
            return $this->failNotFound("User not found");
        }

        // Fetch normal input
        $data = $this->request->getPost();

        // Handle file upload
        $file = $this->request->getFile('id_proof');
        $filePath = $user['id_proof'] ?? null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/id_proof/';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

            $username = $data['username'] ?? $user['username'];
            $newName = strtolower($username) . '.pdf';

            if (file_exists($uploadPath . $newName)) unlink($uploadPath . $newName);
            $file->move($uploadPath, $newName);
            $filePath = 'uploads/id_proof/' . $newName;
        }

        $roles = isset($data['roles']) ? json_decode($data['roles'], true) : $user['roles'];
        $permission = isset($data['permission']) ? json_decode($data['permission'], true) : $user['permission'];

        // Prepare update data
        $updateData = [
            'username'   => $data['username'] ?? $user['username'],
            'email'      => $data['email'] ?? $user['email'],
            'mobile'     => $data['mobile'] ?? $user['mobile'],
            'id_proof'   => $filePath,
            'roles'      => json_encode($roles),
            'permission' => json_encode($permission),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $userModel->update($userId, $updateData);

        return $this->respond([
            'status' => true,
            'message' => 'User updated successfully',
            'user_id' => $userId,
            'id_proof_path' => $filePath,
            'roles' => json_encode($roles),
            'permisssion' => json_encode($permission),
        ]);
    }

    /**
     * Delete user
     */
    public function delete($id = null)
    {
        $user = $this->model->find($id);
        if (!$user) {
            return $this->failNotFound('User not found');
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'status' => 'User deleted',
            'user_id' => $id
        ]);
    }

    public function login()
    {
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        if (empty($username) || empty($password)) {
            return $this->fail('Username and password are required');
        }

        $user = $this->model
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return $this->fail('Invalid password');
        }
        helper('jwt');

        // Prepare JWT payload
        $tokenData = [
            'user_id'  => $user['user_id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'roles'    => json_decode($user['roles'], true),
            'permission'    => json_decode($user['permission'], true),
        ];

        $jwt = generateJWT($tokenData);

        $loginModel = new \App\Models\AdminLoginAttemptsModel();
        $loginModel->insert([
            "ip_address" => $this->request->getIPAddress(),
            "user_id" => $user['user_id'],
            "username" => $user['username'],
        ]);


        return $this->respond([
            'status' => true,
            'message' => 'Login successful',
            'user'   => [
                'user_id'  => $user['user_id'],
                'username' => $user['username'],
                'email'    => $user['email'],
            ],
            'token'  => $jwt
        ]);
    }

    public function logout($id)
    {
        $loginModel = new \App\Models\AdminLoginAttemptsModel();

        // Check if the login record exists
        $data = $loginModel->find($id);

        if (!$data) {
            return $this->respond([
                "status" => false,
                "message" => "No record found."
            ], 404);
        }

        $updateData = ['logout_time' => date('Y-m-d H:i:s')];

        // Only update if there is valid data
        if (!empty($updateData)) {
            $updated = $loginModel->update($id, $updateData);

            if ($updated) {
                return $this->respond([
                    "status" => true,
                    "message" => "Logout successfully."
                ]);
            }
        }

        return $this->respond([
            "status" => false,
            "message" => "Logout failed."
        ], 500);
    }

    // Edit admin profile
    public function updateAdmin($id)
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->respond([
                'status' => false,
                'message' => 'No data provided'
            ], 400);
        }

        // Update the admin data
        $updated = $this->model->update($id, [
            'email'    => $data['email'] ?? null,
            'password' => isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null

        ]);

        if ($updated) {
            // Refetch updated user data
            $user = $this->model->find($id);

            if (!$user) {
                return $this->respond([
                    'status' => false,
                    'message' => 'User not found after update'
                ], 500);
            }

            // Generate new JWT token
            helper('jwt');
            $token = generateJWT([
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]);

            return $this->respond([
                'status' => true,
                'message' => 'Profile updated successfully',
                'token' => $token,
            ]);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }
}
