<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Cors implements FilterInterface
{
  /**
   * Do whatever processing this filter needs to do.
   * By default it should not return anything during
   * normal execution. However, when an abnormal state
   * is found, it should return an instance of
   * CodeIgniter\HTTP\Response. If it does, script
   * execution will end and that Response will be
   * sent back to the client, allowing for error pages,
   * redirects, etc.
   *
   * @param RequestInterface $request
   * @param array|null       $arguments
   *
   * @return RequestInterface|ResponseInterface|string|void
   */

  /*protected $allowedOrigins = [
    '*',
  ];*/
  protected $allowedOrigins = [
    '*',
  ];

  public function before(RequestInterface $request, $arguments = null)
  {

    // $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $origin = $request->getHeaderLine('Origin');
    
    if ($origin === 'https://rentoconnect.propheticdevelopers.com') {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY, X-Requested-With");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Credentials: true");
    }
    if ($origin === 'https://www.rentoconnect.propheticdevelopers.com') {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY, X-Requested-With");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Credentials: true");
    }
    if ($origin === 'http://localhost:5173') {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY, X-Requested-With");
        header("Access-Control-Allow-Methods:  GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Credentials: true");
    }
    // else if (in_array($origin, $this->allowedOrigins)) {
    //   header('Access-Control-Allow-Origin: ' . $origin);
    //   header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    //   header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
    //   header('Access-Control-Allow-Credentials: true');
    // }

    if ($request->getMethod() === 'options') {
      http_response_code(200);
      exit();
    }
  }

  /**
   * Allows After filters to inspect and modify the response
   * object as needed. This method does not allow any way
   * to stop execution of other after filters, short of
   * throwing an Exception or Error.
   *
   * @param RequestInterface  $request
   * @param ResponseInterface $response
   * @param array|null        $arguments
   *
   * @return ResponseInterface|void
   */
  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    // $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // if (in_array($origin, $this->allowedOrigins)) {
    //   $response->setHeader('Access-Control-Allow-Origin', $origin)
    //     ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
    //     ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-CSRF-Token')
    //     ->setHeader('Access-Control-Allow-Credentials', 'true');
    // }

    return $response;
  }
}
