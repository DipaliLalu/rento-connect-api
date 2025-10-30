<?php

namespace App\Controllers;

use App\Models\ProductModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class ProductController extends ResourceController
{
    protected $modelName = ProductModel::class;
    protected $format    = 'json';

    // ✅ Get all products
    public function index()
    {
        $products = $this->model->where('deleted', 0)->where('active', 1)->findAll();
        return $this->respond([
            'response' => true,
            'data' => $products
        ]);
    }

    // ✅ Create product
    public function create()
    {
        $rules = [
            'product_name' => 'required|min_length[3]',
            'price_hour'   => 'required|decimal',
            'price_day'    => 'required|decimal',
            'description'  => 'required',
            'metadata'      => 'required',
            'metatag'       => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Text fields
        $data = $this->request->getVar();
        $slug = $this->generateSlug($data['product_name']);
        $slugImage = strtolower(str_replace(' ', '_', $data['product_name']));

        // Image upload
        $product_image = $this->request->getFile('product_image');
        $imagePath = null;

        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';

        if ($product_image && $product_image->isValid() && !$product_image->hasMoved()) {
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $newName = $slugImage . '.webp';
            $product_image->move($uploadPath, $newName);

            $imagePath = 'uploads/products/' . $newName;
        }

        $insertData = [
            'product_name'  => $data['product_name'],
            'slug'          => $slug,
            'price_hour'    => $data['price_hour'],
            'price_day'     => $data['price_day'],
            'description'   => $data['description'],
            'product_image' => $imagePath,
            'metadata'       => $data['metadata'],
            'metatag'        => $data['metatag'],
            'active'         => $data['active'],
        ];

        $this->model->insert($insertData);

        cache()->delete('products_all');

        return $this->respondCreated([
            'status'  => true,
            'message' => 'Product created successfully',
            'image'   => $imagePath
        ]);
    }

    // ✅ Get single product
    public function show($id = null)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return $this->respond([
                'response' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return $this->respond([
            'response' => true,
            'data' => $product
        ]);
    }

    // ✅ Update product
    public function update($id = null)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return $this->respond([
                'response' => false,
                'message'  => 'Product not found'
            ], 404);
        }

        // Handle form-data or JSON
        $data = $this->request->getVar();
        if (empty($data)) {
            $data = $this->request->getRawInput();
        }

        $slugImage = strtolower(str_replace(' ', '_', $data['product_name'] ?? $product['product_name']));
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/';

        $image = $this->request->getFile('product_image');
        $imagePath = $product['product_image']; // default to old

        if ($image && $image->isValid() && !$image->hasMoved()) {
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $product['product_image'];
            if (!empty($product['product_image']) && file_exists($oldImagePath) && is_file($oldImagePath)) {
                unlink($oldImagePath);
            }

            $newFileName = $slugImage . '.webp';

            // ✅ Convert and save to .webp
            Services::image()
                ->withFile($image)
                ->save($uploadPath . $newFileName, 80, 'webp');

            $imagePath = 'uploads/products/' . $newFileName;
        }

        // ✅ Safe partial update
        $updateData = array_filter([
            'product_name'  => $data['product_name'] ?? $product['product_name'],
            'price_hour'    => $data['price_hour'] ?? $product['price_hour'],
            'price_day'     => $data['price_day'] ?? $product['price_day'],
            'description'   => $data['description'] ?? $product['description'],
            'product_image' => $imagePath,
            'metadata'      => $data['metadata'] ?? $product['metadata'],
            'metatag'       => $data['metatag'] ?? $product['metatag'],
            'active'        => $data['active'] ?? $product['active'],
        ], fn($v) => $v !== null);

        $this->model->update($id, $updateData);

        cache()->delete('products_all');

        return $this->respond([
            'response' => true,
            'message'  => 'Product updated successfully',
            'data'     => $updateData
        ]);
    }

    // ✅ Delete product
    public function delete($id = null)
    {
        $product = $this->model->find($id);
        if (!$product) {
            return $this->respond([
                'response' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Soft delete
        $this->model->update($id, ['deleted' => 1]);

        // Clear cache
        cache()->delete('products_all');
        cache()->delete('product_' . $id);

        return $this->respondDeleted([
            'response' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    // ✅ Slug Generator
    private function generateSlug($string)
    {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }
}
