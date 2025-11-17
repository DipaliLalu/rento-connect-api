<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Models\MobilitySubServicesModel;

class MobilitySubServicesController extends ResourceController
{
    protected $modelName = MobilitySubServicesModel::class;
    protected $format    = 'json';

    private function generateUniqueSlug(string $name): string
    {
        $slug = strtolower(url_title($name, '-', true));
        $originalSlug = $slug;
        $count = 1;

        while ($this->model->where('slug', $slug)->first()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public function create()
    {
        $validation = service('validation');
        $data = $this->request->getJSON(true);

        $validation->setRules([
            'display_name' => 'required|string',
        ]);

        if (!$validation->run($data)) {
            return $this->respond([
                'response' => false,
                'message'  => $validation->getErrors(),
            ], 422);
        }

        $slug = $this->generateUniqueSlug($data['display_name']);

        $insertData = [
            'display_name' => $data['display_name'],
            'slug'         => $slug,
        ];

        try {
            $this->model->insert($insertData);
            $id = $this->model->getInsertID();
            $created = $this->model->find($id);

            return $this->respondCreated([
                'response' => true,
                'message'  => 'Inserted successfully!',
                'data'     => $created,
            ]);
        } catch (DatabaseException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return $this->respond([
                    'response' => false,
                    'message'  => 'Slug already exists.',
                ], 409);
            }

            return $this->respond([
                'response' => false,
                'message'  => 'Failed to add mobility sub service.',
                'error'    => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $permissions = $this->model->findAll();
        return $this->respond([
            'response' => true,
            'data'     => $permissions,
            'message'  => 'Services fetched successfully'
        ]);
    }
}
