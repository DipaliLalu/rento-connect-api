<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiKey implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Fetch API key from header
        $apiKey = $request->getHeaderLine('X-API-KEY');

        // Fetch your secret key from .env
        $secretKey = getenv('API_KEY');

        if ($apiKey !== $secretKey) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'response' => false,
                    'message'  => 'Invalid API Key',
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing to do
    }
}
