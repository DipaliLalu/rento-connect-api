<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class BlogController extends ResourceController
{
    protected $modelName = "App\Models\BlogModel";
    protected $format = "json";

    // ✅ Helper to generate slug
    private function generateSlug($title)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }

    // ✅ Add Blog
    public function addBlog()
    {
        helper(['form']);

        $fields = ['title', 'description', 'active', 'metadata', 'metatag'];
        $rules = [];

        foreach ($fields as $field) {
            $rules[$field] = [
                'rules' => 'required',
                'errors' => ['required' => "$field is required"],
            ];
        }

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $slug = $this->generateSlug($data['title']);
        $slugImage = str_replace(' ', '_', strtolower($data['title']));

        $image = $this->request->getFile('image');
        $imagePath = null;

        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/blog/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $slugImage . '.webp';
            $image->move($uploadPath, $newName);
            $imagePath = 'uploads/blog/' . $newName;
        }

        $insertData = [
            'title'       => $data['title'],
            'description' => $data['description'],
            'slug'        => $slug,
            'image'       => $imagePath,
            'active'      => $data['active'],
            'metadata'    => $data['metadata'],
            'metatag'     => $data['metatag'],
        ];

        if ($this->model->insert($insertData)) {
            return $this->respond([
                "status"  => true,
                "message" => "Blog added successfully"
            ]);
        }

        return $this->fail("Failed to add blog");
    }

    // ✅ Edit Blog
    public function editBlog($blog_id)
    {
        helper(['form', 'filesystem']);

        $blog = $this->model->find($blog_id);
        if (!$blog) {
            return $this->failNotFound("Blog not found");
        }

        $fields = ['title', 'description', 'active', 'metadata', 'metatag'];
        $rules = [];
        foreach ($fields as $field) {
            $rules[$field] = [
                'rules' => 'required',
                'errors' => ['required' => "$field is required"],
            ];
        }

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $slug = $this->generateSlug($data['title']);
        $slugImage = str_replace(' ', '_', strtolower($data['title']));

        $image = $this->request->getFile('image');
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/blog/';
        $imagePath = $blog['image']; // fallback to existing

        if ($image && $image->isValid() && !$image->hasMoved()) {
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Delete old image if exists
            if (!empty($blog['image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $blog['image'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $blog['image']);
            }

            $newName = $slugImage . '.webp';
            $image->move($uploadPath, $newName);
            $imagePath = 'uploads/blog/' . $newName;
        }

        $updateData = [
            'title'       => $data['title'],
            'description' => $data['description'],
            'image'       => $imagePath,
            'active'      => $data['active'],
            'metadata'    => $data['metadata'],
            'metatag'     => $data['metatag'],
        ];

        if ($this->model->update($blog_id, $updateData)) {
            return $this->respond([
                "status"  => true,
                "message" => "Blog updated successfully"
            ]);
        }

        return $this->fail("Failed to update blog");
    }

    // ✅ List Blogs
    public function listBlogs()
    {
        $blogs = $this->model
            ->where('deleted', 0)
            ->where('active',1)
            ->orderBy('id', 'DESC')
            ->findAll();

        if (!$blogs) {
            return $this->failNotFound('Blog not found.');
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Blogs fetched successfully',
            'data'    => $blogs,
        ]);
    }

    // ✅ Soft Delete Blog
    public function singleDeletedBlog($blog_id)
    {
        if ($this->model->update($blog_id, ['deleted' => 1])) {
            return $this->respond([
                'status' => true,
                'message' => 'Blog deleted successfully',
            ]);
        }

        return $this->fail('Failed to delete blog');
    }
}
