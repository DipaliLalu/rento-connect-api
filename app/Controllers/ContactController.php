<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class ContactController extends ResourceController
{
     public function send_mail()
    {
        helper(['form']);

        $request = \Config\Services::request();
        $emailService = \Config\Services::email();

        $data = [
            'name'         => $request->getPost('name'),
            'email'        => $request->getPost('email'),
            'subject'      => $request->getPost('subject'),
            'message'      => $request->getPost('message'),
        ];

        // Load view as email body
        $message = view('emails/contact_us', $data);

        // Always send from your own verified address (Gmail etc.)
        $emailService->setFrom('propheticdeveloper@gmail.com', 'Rento Connect');
        $emailService->setReplyTo($data['email'], $data['name']); // This is safe
        $emailService->setTo('propheticdeveloper@gmail.com'); // Send to admin
        $emailService->setSubject('Rento Connect - Contact Form Submission');
        $emailService->setMessage($message);
        $emailService->setMailType('html');

        if ($emailService->send()) {
            return $this->response->setJSON(['result' => 'success']);
        } else {
            return $this->response->setJSON(['result' => 'error', 'debug' => $emailService->printDebugger(['headers'])]);
        }
    }
}
