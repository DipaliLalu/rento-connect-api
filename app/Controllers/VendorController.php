<?php

namespace App\Controllers;

use App\Models\VendorModel;
use CodeIgniter\RESTful\ResourceController;

class VendorController extends ResourceController
{
    protected $format = 'json';

    public function register()
    {
        $request = service('request');

        // ✅ Validation Rules
        $validation = service('validation');
        $validation->setRules([
            'name'                => 'required|string',
            'contact'             => 'required|min_length[10]|is_unique[vendors.contact]',
            'alternativecontact'  => 'permit_empty|min_length[10]',
            'email'               => 'required|valid_email|is_unique[vendors.email]',
            'password'            => 'required|min_length[6]',
            'category'            => 'required|string',
            'address'             => 'required|string',
            'city'               => 'required|string',
            'state'               => 'required|string',
            'pincode'               => 'required|string',
            'gstin'               => 'permit_empty|string',
            'experience'          => 'permit_empty|string',
            'location'            => 'required|string',
            'availability'        => 'required|string',
            'id_proof'            => 'permit_empty|uploaded[id_proof]|max_size[id_proof,2048]|ext_in[id_proof,png,jpg,jpeg,pdf]',
        ]);

        if (!$validation->withRequest($request)->run()) {
            return $this->respond([
                'status'  => 'error',
                'message' => $validation->getErrors()
            ], 422);
        }

        // ✅ Handle File Upload (id_proof)
        $file = $this->request->getFile('id_proof');
        $filePath = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Get original extension
            $ext = $file->getExtension(); // png, jpg, jpeg, pdf

            // Create a new unique name (using name + timestamp for uniqueness)
            $newName = $request->getPost('name')  . '.' . $ext;

            // Move file to uploads folder
            $file->move($_SERVER['DOCUMENT_ROOT'] . '/uploads/id_proofs', $newName);

            $filePath = 'uploads/id_proofs/' . $newName;
        }


        // ✅ Save to DB
        $vendorModel = new VendorModel();
        $vendorData = [
            'name'               => $request->getPost('name'),
            'contact'            => $request->getPost('contact'),
            'alternativecontact' => $request->getPost('alternativecontact'),
            'email'              => $request->getPost('email'),
            'password'           => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
            'category'           => $request->getPost('category'),
            'address'            => $request->getPost('address'),
            'state'              => $request->getPost('state'),
            'city'               => $request->getPost('city'),
            'pincode'            => $request->getPost('pincode'),
            'gstin'              => $request->getPost('gstin'),
            'experience'         => $request->getPost('experience'),
            'location'           => $request->getPost('location'),
            'availability'       => $request->getPost('availability'),
            'id_proof'           => $filePath,
            'active'             => 0,
            'deleted'            => 0,
        ];

        $vendorModel->insert($vendorData);

        // ✅ Send Registration Email
        $email = \Config\Services::email();
        $email->setTo($request->getPost('email'));
        $email->setSubject('Account Pending Approval');
        $email->setMessage("
        Hi {$request->getPost('name')},<br><br>
        Thank you for registering with us.<br>
        Your account will be activated within 3 days.<br><br>
        Regards,<br>Team Rento Connect
    ");
        $email->send();

        return $this->respondCreated([
            'response' => true,
            'message' => 'Vendor registered successfully and email sent.'
        ]);
    }

    public function activateVendor($id)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->find($id);

        if (!$vendor) {
            return $this->respond(['message' => 'Vendor not found'], 404);
        }

        $vendorModel->update($id, ['active' => 1]);

        // ✅ Send Activation Email
        $email = \Config\Services::email();
        $email->setTo($vendor['email']);
        $email->setSubject('Your Account is Now Active');
        $email->setMessage("
        Hi {$vendor['name']},<br><br>
        Your account has been activated successfully.<br>
        You can now login and use all services.<br><br>
        Regards,<br>Team
    ");
        $email->send();

        return $this->respond([
            'response' => true,
            'message' => 'Vendor activated and email sent.'
        ]);
    }

    public function getActiveVendors()
    {
        $vendorModel = new VendorModel();

        // Allowed categories
        $allowedCategories = ['Equipment', 'Experts', 'Mobility'];

        // Get query param (?category=equipment,experts,...)
        $category = $this->request->getGet('category');

        if ($category) {
            $categories = explode(',', $category);
            // Filter only categories that are allowed
            $filteredCategories = array_intersect(
                array_map('ucfirst', array_map('strtolower', $categories)), // normalize
                $allowedCategories
            );

            if (!empty($filteredCategories)) {
                $vendorModel->whereIn('category', $filteredCategories);
            } else {
                // If none of the requested categories are allowed, return empty
                $vendors = $vendorModel
                    ->where('active', 1)
                    ->where('deleted', 0)
                    ->findAll();

                return $this->respond([
                    'response' => true,
                    'data'     => $vendors
                ], 200);
            }
        } else {
            // No category param — only return allowed ones
            $vendorModel->whereIn('category', $allowedCategories);
        }

        $vendors = $vendorModel
            ->where('active', 1)
            ->where('deleted', 0)
            ->findAll();

        return $this->respond([
            'response' => true,
            'data'     => $vendors
        ], 200);
    }

    public function getInactiveVendors()
    {
        $vendorModel = new VendorModel();
        // Allowed categories
        $allowedCategories = ['Equipment', 'Experts', 'Mobility'];
        $category = $this->request->getGet('category');

        if ($category) {
            $categories = explode(',', $category);
            // Filter only categories that are allowed
            $filteredCategories = array_intersect(
                array_map('ucfirst', array_map('strtolower', $categories)), // normalize
                $allowedCategories
            );

            if (!empty($filteredCategories)) {
                $vendorModel->whereIn('category', $filteredCategories);
            } else {
                // If none of the requested categories are allowed, return empty
                $vendors = $vendorModel
                    ->where('active', 0)
                    ->where('deleted', 0)
                    ->findAll();

                return $this->respond([
                    'response' => true,
                    'data'     => $vendors
                ], 200);
            }
        } else {
            // No category param — only return allowed ones
            $vendorModel->whereIn('category', $allowedCategories);
        }

        $vendors = $vendorModel
            ->where('active', 0)
            ->where('deleted', 0)
            ->findAll();

        return $this->respond([
            'response' => true,
            'data'     => $vendors
        ], 200);
        // if ($category) {
        //     $categories = explode(',', $category);
        //     $vendorModel->whereIn('category', $categories);
        // }
        // $vendors = $vendorModel->where('active', 0)->where('deleted', 0)->findAll();

        // return $this->respond([
        //     'response' => true,
        //     'data'     => $vendors
        // ], 200);
    }

    public function login()
    {
        $request = service('request');
        $validation = service('validation');

        // ✅ Validation
        $validation->setRules([
            'email'    => 'required|valid_email',
            'password' => 'required'
        ]);

        if (!$validation->withRequest($request)->run()) {
            return $this->respond([
                'response' => false,
                'message'  => $validation->getErrors()
            ], 422);
        }

        $email = $request->getPost('email');
        $password = $request->getPost('password');

        $vendorModel = new \App\Models\VendorModel();
        $customerModel = new \App\Models\CustomerRegisterModel();

        $user = null;
        $role = null;

        // ✅ First check Vendor table
        $vendor = $vendorModel
            ->groupStart()
            ->where('email', $email)
            ->orWhere('contact', $email)
            ->groupEnd()
            ->where('deleted', 0)
            ->first();

        if ($vendor) {
            $user = $vendor;
            $role = 'vendor';
        } else {
            // ✅ Then check Customer table
            $customer = $customerModel->where('email', $email)
                ->where('deleted', 0)
                ->first();
            if ($customer) {
                $user = $customer;
                $role = 'customer';
            }
        }

        // ✅ Email not found in either
        if (!$user) {
            return $this->respond([
                'response' => false,
                'message'  => 'Email not found'
            ], 404);
        }

        // ✅ Active check (if exists)
        if (isset($user['active']) && $user['active'] != 1) {
            return $this->respond([
                'response' => false,
                'message'  => 'Your account is not active yet. Please wait for approval.'
            ], 403);
        }

        // ✅ Password verify
        if (!password_verify($password, $user['password'])) {
            return $this->respond([
                'response' => false,
                'message'  => 'Incorrect password'
            ], 401);
        }

        // ✅ JWT token
        helper('jwt');
        $token = generateJWT([
            'id' => $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
            'role' => $role,
            'data' => $user
        ]);

        // ✅ Success response
        return $this->respond([
            'response' => true,
            'message'  => ucfirst($role) . ' login successful',
            'token'    => $token,
            'role'     => $role,
            'data'     => $user
        ], 200);
    }
}
