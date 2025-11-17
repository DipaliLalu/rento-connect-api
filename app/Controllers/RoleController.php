<?php

namespace App\Controllers;

use App\Models\RolesModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class RoleController extends ResourceController
{
    protected $modelName = RolesModel::class;
    protected $format    = 'json';

    // GET /roles
    public function index()
    {
        $roles = $this->model->findAll();
        return $this->respond([
            'response' => true,
            'data'     => $roles,
            'message'  => 'Roles fetched successfully'
        ]);
    }

    // GET /roles/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Roles not found');
        }

        return $this->respond([
            'response' => true,
            'data'     => $data,
        ]);
    }

    // POST /roles
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['role_name'])) {
            return $this->failValidationErrors('Role name is required');
        }

        $this->model->insert([
            'role_name' => $data['role_name'],
        ]);

        return $this->respondCreated([
            'response' => true,
            'message'  => 'Role created successfully',
        ]);
    }

    // PUT/PATCH /roles/{id}
    public function update($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Role not found');
        }

        $data = $this->request->getJSON(true);
        if (empty($data['role_name'])) {
            return $this->failValidationErrors('Role name is required');
        }

        $this->model->update($id, [
            'role_name' => $data['role_name'],
        ]);

        return $this->respondUpdated([
            'response' => true,
            'message'  => 'Role updated successfully',
        ]);
    }

    // DELETE /roles/{id}
    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Role not found');
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'response' => true,
            'message'  => 'Role deleted successfully',
        ]);
    }
}
