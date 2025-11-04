<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\BookingModel;
use CodeIgniter\RESTful\ResourceController;

class BookingController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $bookingModel = new Booking();
        $bookings = $bookingModel->where(['deleted' => 0])->findAll();

        return $this->respond([
            'response' => true,
            'data'     => $bookings,
        ]);
    }

    public function register()
    {
        $request = service('request');
        $validation = service('validation');

        // ✅ Validation rules (based on your migration)
        $validation->setRules([
            'customer_id' => 'required|integer',
            'name'        => 'required|string|min_length[2]',
            'contact'     => 'required|string|min_length[10]',
            'email'       => 'required|valid_email',
            'subcategory' => 'required|string',
            'gstin'       => 'permit_empty|string',
            'description' => 'required|string',
            'location'    => 'required|string',
            'startDate'   => 'required|string',
            'endDate'     => 'required|string',
        ]);

        // ❌ If validation fails
        if (!$validation->withRequest($request)->run()) {
            return $this->respond([
                'response' => false,
                'message'  => $validation->getErrors(),
            ], 422);
        }

        // ✅ Prepare data
        $data = [
            'customer_id' => $request->getPost('customer_id'),
            'name'        => $request->getPost('name'),
            'contact'     => $request->getPost('contact'),
            'email'       => $request->getPost('email'),
            'subcategory' => $request->getPost('subcategory'),
            'category'    => $request->getPost('category'),
            'gstin'       => $request->getPost('gstin'),
            'description' => $request->getPost('description'),
            'location'    => $request->getPost('location'),
            'startDate'   => $request->getPost('startDate'),
            'endDate'     => $request->getPost('endDate'),
            'active'      => 1,
            'deleted'     => 0,
        ];

        $bookingModel = new Booking();

        try {
            $inserted = $bookingModel->insert($data);

            if (!$inserted) {
                return $this->respond([
                    'response' => false,
                    'message'  => 'Failed to create booking. Please try again.',
                ], 500);
            }

            return $this->respondCreated([
                'response' => true,
                'message'  => 'Booking created successfully!',
                'booking_id' => $bookingModel->getInsertID(),
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'response' => false,
                'message'  => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 🔵 Get a specific booking by ID
     */
    public function show($id = null)
    {
        $bookingModel = new Booking();

        // ✅ Validate input
        if ($id === null) {
            return $this->failValidationError('Booking ID is required');
        }

        // ✅ Get the booking by ID
        $booking = $bookingModel->find($id);

        if (!$booking) {
            return $this->failNotFound('Booking not found');
        }

        // ✅ Get all bookings by the same customer
        $customerBookings = $bookingModel
            ->where('customer_id', $booking['customer_id'])
            ->findAll();

        // ✅ Respond with the list
        return $this->respond([
            'response' => true,
            'data'     => $customerBookings,
        ]);
    }

    public function activateBooking($id)
    {
        $model = new Booking();
        $booking = $model->find($id);

        if (!$booking) {
            return $this->respond(['message' => 'Booking not found'], 404);
        }

        $model->update($id, ['active' => 1]);

        return $this->respond([
            'response' => true,
            'message' => 'Booking activated'
        ]);
    }

    public function getActiveBookings()
    {
        $model = new Booking();

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
                $model->whereIn('category', $filteredCategories);
            } else {
                // If none of the requested categories are allowed, return empty
                $vendors = $model
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
            $model->whereIn('category', $allowedCategories);
        }

        $vendors = $model
            ->where('active', 1)
            ->where('deleted', 0)
            ->findAll();

        return $this->respond([
            'response' => true,
            'data'     => $vendors
        ], 200);

        // if ($category) {
        //     $categories = explode(',', $category);
        //     $model->whereIn('category', $categories);
        // }
        // $bookings = $model->where('active', 1)->where('deleted', 0)->findAll();

        // return $this->respond([
        //     'response' => true,
        //     'data'     => $bookings
        // ], 200);
    }

    public function getInactiveVendors()
    {
        $model = new Booking();
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
                $model->whereIn('category', $filteredCategories);
            } else {
                // If none of the requested categories are allowed, return empty
                $vendors = $model
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
            $model->whereIn('category', $allowedCategories);
        }

        $vendors = $model
            ->where('active', 0)
            ->where('deleted', 0)
            ->findAll();

        return $this->respond([
            'response' => true,
            'data'     => $vendors
        ], 200);
        // $bookings = $model->where('active', 0)->where('deleted', 0)->findAll();

        // return $this->respond([
        //     'response' => true,
        //     'data'     => $bookings
        // ], 200);
    }

    // DELETE 
    public function delete($id = null)
    {
        $bookingModel = new Booking();
        $booking = $bookingModel->find($id);

        if (!$booking) {
            return $this->failNotFound('Booking not found');
        }

        $bookingModel->update($id, ['deleted' => 1]);

        return $this->respond([
            'response' => true,
            'message'  => 'Booking deleted successfully',
        ]);
    }
}
