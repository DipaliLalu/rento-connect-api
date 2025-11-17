<?php

namespace App\Controllers;

use App\Models\PermissionModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class PermissionController extends ResourceController
{
    protected $modelName = PermissionModel::class;
    protected $format    = 'json';

    // GET /permissions
    public function index()
    {
        $permissions = $this->model->findAll();
        return $this->respond([
            'response' => true,
            'data'     => $permissions,
            'message'  => 'Permissions fetched successfully'
        ]);
    }

    // GET /permissions/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data) {
            return $this->failNotFound('Permission not found');
        }

        return $this->respond([
            'response' => true,
            'data'     => $data,
        ]);
    }

    // POST /permissions
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['permission_name'])) {
            return $this->failValidationErrors('Permission name is required');
        }

        $this->model->insert([
            'permission_name' => $data['permission_name'],
        ]);

        return $this->respondCreated([
            'response' => true,
            'message'  => 'Permission created successfully',
        ]);
    }

    // PUT/PATCH /permissions/{id}
    public function update($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Permission not found');
        }

        $data = $this->request->getJSON(true);
        if (empty($data['permission_name'])) {
            return $this->failValidationErrors('Permission name is required');
        }

        $this->model->update($id, [
            'permission_name' => $data['permission_name'],
        ]);

        return $this->respondUpdated([
            'response' => true,
            'message'  => 'Permission updated successfully',
        ]);
    }

    // DELETE /permissions/{id}
    public function delete($id = null)
    {
        if (!$this->model->find($id)) {
            return $this->failNotFound('Permission not found');
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'response' => true,
            'message'  => 'Permission deleted successfully',
        ]);
    }
}
