<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use CodeIgniter\RESTful\ResourceController;
use Config\App;

class CategoryController extends ResourceController
{
    protected $modelName = CategoryModel::class;
    protected $format    = 'json';
    protected $cacheTtl  = 300; // default 5 minutes

    /**
     * Generate slug from category name
     */
    private function generateSlug(string $name): string
    {
        return strtolower(url_title($name, '-', true));
    }

    // GET /categories
    public function index()
    {
        $cacheKey = 'categories_all';
        $data = cache($cacheKey);

        if (!$data) {
            $data = $this->model
                ->where('deleted', 0)
                ->where('active', 1)
                ->findAll();

            cache()->save($cacheKey, $data, $this->cacheTtl);
        }

        return $this->respond([
            'response'   => true,
            'data' => $data,
        ]);
    }

    // GET /categories/{id}
    public function show($category_id = null)
    {
        $cacheKey = 'category_' . $category_id;
        $data = cache($cacheKey);

        if (!$data) {
            $data = $this->model
                ->where('deleted', 0)
                ->where('active', 1)
                ->find($category_id);

            if (!$data) {
                return $this->failNotFound("Category not found");
            }

            cache()->save($cacheKey, $data, $this->cacheTtl);
        }

        return $this->respond([
            'response' => true,
            'category' => $data,
        ]);
    }

    // POST /categories
    public function create()
    {
        $rules = [
            'category_name' => 'required|min_length[3]',
            'metadata'      => 'required',
            'metatag'       => 'required',
            'heading'       => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Text fields
        $data = $this->request->getVar();
        $slug = $this->generateSlug($data['category_name']);
        $slugImage = strtolower(str_replace(' ', '_', $data['category_name']));

        // Image upload
        $category_image = $this->request->getFile('category_image');
        $imagePath = null;

        // Absolute path => public_html/uploads/categories/
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/categories/';

        if ($category_image && $category_image->isValid() && !$category_image->hasMoved()) {
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true); // auto create folder if not exist
            }

            $newName = $slugImage . '.' . 'webp';
            $category_image->move($uploadPath, $newName);

            // Relative path (DB save / frontend access)
            $imagePath = 'uploads/categories/' . $newName;
        }

        // Insert category
        $insertData = [
            'category_name'  => $data['category_name'],
            'slug'           => $slug,
            'heading'        => $data['heading'],
            'description'    => $data['description'] ?? '',
            'category_image' => $imagePath,
            'metadata'       => $data['metadata'],
            'metatag'        => $data['metatag'],
            'active'         => $data['active'],
            'created_by'     => $data['created_by'],
            'title'          => $data['title']
        ];
        $rolesModel = new \App\Models\RolesModel();
        $rolesModel->insert([
            'role_name' => $data['category_name'],
        ]);


        $this->model->insert($insertData);

        // Cache clear
        cache()->delete('categories_all');

        return $this->respondCreated([
            'status' => true,
            'message'  => 'Category created successfully',
            'image'    => $imagePath
        ]);
    }

    // PUT/PATCH /categories/{id}
    public function update($category_id = null)
    {
        $category = $this->model->find($category_id);
        if (!$category) {
            return $this->failNotFound("Category not found");
        }

        // Handle both form-data and JSON
        $data = $this->request->getVar();
        if (empty($data)) {
            $data = $this->request->getRawInput();
        }

        $slugImage = strtolower(str_replace(' ', '_', $data['category_name'] ?? $category['category_name']));

        $image = $this->request->getFile('category_image');

        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Upload destination
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/categories/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // OLD image full path from DB (e.g. "uploads/categories/sports.webp")
            $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $category['category_image'];

            // ✅ Remove old file (png/jpg/webp whatever it is)
            if (!empty($category['category_image']) && file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            // ✅ Generate new image name based on category name
            $slugImage = strtolower(str_replace(' ', '_', $data['category_name'] ?? $category['category_name']));

            $newFileName = $slugImage . '.' . 'webp';

            // ✅ Move new image
            $image->move($uploadPath, $newFileName);

            // ✅ Save new relative path for DB
            $imagePath = 'uploads/categories/' . $newFileName;
        } else {
            // If no new image uploaded, keep old one
            $imagePath = $category['category_image'];
        }

        $updateData = [
            'category_name'  => $data['category_name'] ?? $category['category_name'],
            'heading'        => $data['heading'] ?? $category['heading'],
            'description'    => $data['description'] ?? $category['description'],
            'metadata'       => $data['metadata'] ?? $category['metadata'],
            'metatag'        => $data['metatag'] ?? $category['metatag'],
            'active'         => $data['active'] ?? $category['active'],
            'category_image' => $imagePath,
            'title'          => $data['title'] ?? $category['title'],
        ];

        $this->model->update($category_id, $updateData);

        // Update related role (optional)
        $rolesModel = new \App\Models\RolesModel();
        $existingRole = $rolesModel->where('role_name', $category['category_name'])->first();

        if ($existingRole) {
            $rolesModel->update($existingRole['role_id'], [
                'role_name' => $data['category_name']
            ]);
        } else {
            // create role if not found
            $rolesModel->insert([
                'role_name' => $data['category_name']
            ]);
        }

        cache()->delete('categories_all');
        cache()->delete('category_' . $category_id);

        return $this->respond([
            'response' => true,
            'message'  => 'Category updated successfully',
            'category' => $this->model->find($category_id),
        ]);
    }

    // DELETE /categories/{id}
    public function delete($category_id = null)
    {
        $category = $this->model->find($category_id);
        if (!$category) {
            return $this->failNotFound("Category not found");
        }

        // Soft delete
        $this->model->update($category_id, ['deleted' => 1]);

        // Clear cache
        cache()->delete('categories_all');
        cache()->delete('category_' . $category_id);

        return $this->respondDeleted([
            'response' => true,
            'message'  => 'Category deleted successfully',
        ]);
    }
}
