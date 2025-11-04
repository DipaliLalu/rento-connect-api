<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class BlogController extends ResourceController
{
    protected $modelName = "App\Models\BlogModel";
    protected $format = "json";

    //add blog
    public function addBlog()
    {
        helper(['form']);

        // Fields to validate
        $validateData = [
            'title',
            'description',
            'active',
            'metadata',
            'metatag'
        ];
        $validationRules = [];

        // Dynamically create validation rules
        foreach ($validateData as $field) {
            $validationRules[$field] = [
                'rules' => 'required',
                'errors' => [
                    'required' =>  $field . ' is required',
                ]
            ];
        }

        // Validate inputs
        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Handle uploaded file
        $image = $this->request->getFile("image");
        $imageName = null;

        // Retrieve other form data (multipart/form-data, not JSON)
        $data = $this->request->getPost();

        $slug = strtolower(str_replace(' ', '-', $data['title']));
        $slugImage = strtolower(str_replace(' ', '_', $data['title']));

        if ($image && $image->isValid() && !$image->hasMoved()) {
            $imageName = $slugImage . '_icon.webp';

            // Move to uploads/category/ (in project root, not public)
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . 'uploads/blog/';

            // Ensure directory exists
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $image->move($uploadPath, $imageName);
        }

        $insertData = [
            'title'        => $data['title'],
            'description'  => $data['description'],
            'slug'         => $slug,
            'image'        => 'uploads/blog/' . $imageName,
            'active'       => $data['active'],
            'metadata'     => $data['metadata'],
            'metatag'      => $data['metatag'],
        ];

        // Insert into DB using model
        if ($this->model->insert($insertData)) {
            return $this->respond([
                "status"  => true,
                "message" => "Blog added successfully"
            ]);
        } else {
            return $this->respond([
                "status"  => false,
                "message" => "Failed to add blog"
            ]);
        }
    }

    //edit a blog
    public function editBlog($blog_id)
    {
        helper(['form', 'filesystem']);

        $blog = $this->model->find($blog_id);
        if (!$blog) {
            return $this->failNotFound("Blog not found");
        }

        // Required validation fields
        $validateData = [
            'title',
            'description',
            'active',
            'metadata',
            'metatag'
        ];

        $validationRules = [];
        foreach ($validateData as $field) {
            $validationRules[$field] = [
                'rules' => 'required',
                'errors' => ['required' => "$field is required"]
            ];
        }

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $imageName = $blog['image'];
        $image = $this->request->getFile('image');

        // Handle game icon update
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $uploadPath = $_SERVER['DOCUMENT_ROOT']  . 'uploads/blog/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // Old filename without extension
            $oldFileName = pathinfo($imageName, PATHINFO_FILENAME);

            // New filename with .webp
            $newFileName = $oldFileName . '.webp';

            // Delete old .png if exists
            $oldPng = $uploadPath . $oldFileName . '.png';
            if (file_exists($oldPng)) {
                unlink($oldPng);
            }

            // Move uploaded file as .webp (overwrite if exists)
            $image->move($uploadPath, $newFileName, true);

            // Update db with new .webp filename
            $imageName = $newFileName;
        }

        // Prepare data for DB update
        $updateData = [
            'title'        => $data['title'],
            'description'  => $data['description'],
            'image'        => 'uploads/blog/' . $imageName,
            'active'       => $data['active'],
            'metadata'     => $data['metadata'],
            'metatag'      => $data['metatag'],
        ];


        if ($this->model->update($blog_id, $updateData)) {
            return $this->respond([
                "status"  => true,
                "message" => "Blog updated successfully"
            ]);
        } else {
            return $this->respond([
                "status"  => false,
                "message" => "Failed to update blog"
            ]);
        }
    }

    //list game
    public function listBlogs()
    {
        $blogs = $this->model
            ->where('deleted', 0)->where('active',1)->orderBy('id', 'DESC')
            ->findAll();

        if (!$blogs) {
            return $this->failNotFound('Blog not found.');
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Blog fetched successfully',
            'data'    => $blogs,
        ]);
    }

    //deleted value 
    public function singleDeletedBlog($blog_id)
    {
        $updateData = [
            'deleted' => 1,
        ];
        if ($this->model->update($blog_id, $updateData)) {
            return $this->respond([
                'status' => true,
                'message' => 'Blog deleted successfully',
            ]);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to deleted Blog',
            ], 500);
        }
    }
}
