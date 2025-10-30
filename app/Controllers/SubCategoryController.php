<?php

namespace App\Controllers;

use App\Models\SubCategoryModel;
use CodeIgniter\RESTful\ResourceController;

class SubCategoryController extends ResourceController
{
    protected $modelName = SubCategoryModel::class;
    protected $format    = 'json';
    protected $cacheTtl  = 300; // 5 minutes

    /**
     * Generate slug from subcategory name
     */
    private function generateSlug(string $name): string
    {
        return strtolower(url_title($name, '-', true));
    }

    // GET /subcategories
    public function index()
    {
        $cacheKey = 'subcategories_all';
        $data = cache($cacheKey);

        if (!$data) {
            $data = $this->model
                ->where('deleted', 0)
                ->where('active', 1)
                ->findAll();

            cache()->save($cacheKey, $data, $this->cacheTtl);
        }

        return $this->respond([
            'response' => true,
            'data'     => $data,
        ]);
    }

    // GET /subcategories/{id}
    public function show($id = null)
    {
        $cacheKey = 'subcategory_' . $id;
        $data = cache($cacheKey);

        if (!$data) {
            $data = $this->model
                ->where('deleted', 0)
                ->where('active', 1)
                ->find($id);

            if (!$data) {
                return $this->failNotFound('Subcategory not found');
            }

            cache()->save($cacheKey, $data, $this->cacheTtl);
        }

        return $this->respond([
            'response'    => true,
            'subcategory' => $data,
        ]);
    }

    // POST /subcategories
    public function create()
    {
        $rules = [
            'category_slug' => 'required',
            'subcategory_name' => 'required|min_length[3]',
            'metadata'      => 'required',
            'metatag'       => 'required',
            'heading'       => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        $data = $this->request->getVar();
        $slug = $this->generateSlug($data['subcategory_name']);
        $slugImage = strtolower(str_replace(' ', '_', $data['subcategory_name']));

        // Upload image
        $subcategory_image = $this->request->getFile('subcategory_image');
        $imagePath = null;
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/subcategory/';

        if ($subcategory_image && $subcategory_image->isValid() && !$subcategory_image->hasMoved()) {
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $newName = $slugImage . '.' . $subcategory_image->getExtension();
            $subcategory_image->move($uploadPath, $newName);
            $imagePath = 'uploads/subcategory/' . $newName;
        }

        $insertData = [
            'category_slug'  => $data['category_slug'],
            'subcategory_name'  => $data['subcategory_name'],
            'slug'           => $slug,
            'heading'        => $data['heading'],
            'description'    => $data['description'] ?? '',
            'subcategory_image' => $imagePath,
            'metadata'       => $data['metadata'],
            'metatag'        => $data['metatag'],
            'active'         => $data['active'] ?? 1,
            'created_by'     => $data['created_by'],
        ];

        $this->model->insert($insertData);

        cache()->delete('subcategories_all');

        return $this->respondCreated([
            'status'  => true,
            'message' => 'Subcategory created successfully',
            'image'   => $imagePath,
        ]);
    }

    // PUT /subcategories/{id}
    public function update($id = null)
    {
        $subcategory = $this->model->find($id);
        if (!$subcategory) {
            return $this->failNotFound('Subcategory not found');
        }

        $data = $this->request->getVar();
        if (empty($data)) {
            $data = $this->request->getRawInput();
        }

        $slug = $this->generateSlug($data['subcategory_name'] ?? $subcategory['subcategory_name']);
        $slugImage = strtolower(str_replace(' ', '_', $data['subcategory_name'] ?? $subcategory['subcategory_name']));

        // Upload image
        $subcategory_image = $this->request->getFile('subcategory_image');
        $imagePath = $subcategory['subcategory_image'];

        if ($subcategory_image && $subcategory_image->isValid() && !$subcategory_image->hasMoved()) {
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/subcategory/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if (!empty($subcategory['subcategory_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $subcategory['subcategory_image'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $subcategory['subcategory_image']);
            }

            $newName = $slugImage . '.' . $subcategory_image->getExtension();
            $subcategory_image->move($uploadPath, $newName);
            $imagePath = 'uploads/subcategory/' . $newName;
        }

        $updateData = [
            'category_slug'  => $data['category_slug'] ?? $subcategory['category_slug'],
            'subcategory_name'  => $data['subcategory_name'] ?? $subcategory['subcategory_name'],
            'slug'           => $slug,
            'heading'        => $data['heading'] ?? $subcategory['heading'],
            'description'    => $data['description'] ?? $subcategory['description'],
            'metadata'       => $data['metadata'] ?? $subcategory['metadata'],
            'metatag'        => $data['metatag'] ?? $subcategory['metatag'],
            'active'         => $data['active'] ?? $subcategory['active'],
            'subcategory_image' => $imagePath,
        ];

        $this->model->update($id, $updateData);

        cache()->delete('subcategories_all');
        cache()->delete('subcategory_' . $id);

        return $this->respond([
            'response'    => true,
            'message'     => 'Subcategory updated successfully',
            'subcategory' => $this->model->find($id),
        ]);
    }

    // DELETE /subcategories/{id}
    public function delete($id = null)
    {
        $subcategory = $this->model->find($id);
        if (!$subcategory) {
            return $this->failNotFound('Subcategory not found');
        }

        $this->model->update($id, ['deleted' => 1]);

        cache()->delete('subcategories_all');
        cache()->delete('subcategory_' . $id);

        return $this->respondDeleted([
            'response' => true,
            'message'  => 'Subcategory deleted successfully',
        ]);
    }

    public function getBySlug($slug)
    {
        $category = $this->model->where('category_slug', $slug)->findAll();

        if (!$category) {
            return $this->respond([
                'status' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return $this->respond([
            'status' => true,
            'data'   => $category
        ], 200);
    }
}
