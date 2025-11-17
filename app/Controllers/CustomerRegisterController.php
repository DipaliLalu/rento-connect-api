<?php

namespace App\Controllers;

use App\Models\CustomerRegisterModel;
use CodeIgniter\RESTful\ResourceController;

class CustomerRegisterController extends ResourceController
{
    public function register()
    {
        $request = service('request');
        $validation = service('validation');

        // ✅ Validation Rules
        $validation->setRules([
            'name'               => 'required|string',
            'contact'            => 'required|min_length[10]',
            'alternativecontact' => 'permit_empty|min_length[10]',
            'email'              => 'required|valid_email|is_unique[customer_register.email]',
            'password'           => 'required|min_length[6]',
            'category'           => 'required|string',
            'address'            => 'required|string',
            'city'               => 'required|string',
            'state'              => 'required|string',
            'pincode'            => 'required|string',
            'gstin'              => 'permit_empty|string',
            'location'           => 'required|string',
            'service_frequency'  => 'required|string',
        ]);

        // ❌ Validation failed
        if (!$validation->withRequest($request)->run()) {
            return $this->respond([
                'response' => false,
                'message'  => $validation->getErrors(),
            ], 422);
        }

        // ✅ Prepare insert data
        $customerModel = new CustomerRegisterModel();

        $insertData = [
            'name'               => $request->getPost('name'),
            'contact'            => $request->getPost('contact'),
            'alternativecontact' => $request->getPost('alternativecontact'),
            'email'              => $request->getPost('email'),
            'password'           => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
            'category'           => $request->getPost('category'),
            'address'            => $request->getPost('address'),
            'city'               => $request->getPost('city'),
            'state'              => $request->getPost('state'),
            'pincode'            => $request->getPost('pincode'),
            'gstin'              => $request->getPost('gstin'),
            'location'           => $request->getPost('location'),
            'service_frequency'  => $request->getPost('service_frequency'), // ✅ fixed
            'active'             => 1,
            'deleted'            => 0,
        ];

        // ✅ Try insert
        try {
            $customerModel->insert($insertData);
        } catch (\Exception $e) {
            // Handle duplicate email or DB error
            return $this->respond([
                'response' => false,
                'message'  => 'Failed to register customer: ' . $e->getMessage(),
            ], 500);
        }

        // ✅ Success
        return $this->respondCreated([
            'response' => true,
            'message'  => 'Customer registered successfully!',
        ]);
    }

    public function show($id = null)
    {
        $model = new CustomerRegisterModel();

        // Fetch single active, non-deleted customer
        $data = $model
            ->where('deleted', 0)
            ->where('active', 1)
            ->find($id);

        if (!$data) {
            return $this->failNotFound("Customer not found");
        }

        return $this->respond([
            'response' => true,
            'customer' => $data,
        ]);
    }
}
