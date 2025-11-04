<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->options('(:any)', function () {
    $response = service('response');
    return $response
        ->setStatusCode(200)
        ->setHeader('Access-Control-Allow-Origin', '*')
        ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
        ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-KEY, X-Requested-With');
});

$routes->group('', ['filter' => ['apikey', 'cors']], function ($routes) {

    //category
    $routes->resource('categories', [
        'controller' => 'CategoryController',
        'only' => ['index', 'show', 'delete']
    ]);
    $routes->post('categories', 'CategoryController::create');
    $routes->post('categories/(:num)', 'CategoryController::update/$1');
    $routes->options('categories', static function () {
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');
        return $response;
    });

    // ===== User Routes =====
    $routes->get('users', 'UserController::index');           // List all users
    $routes->get('users/(:num)', 'UserController::show/$1'); // Get single user
    $routes->post('users', 'UserController::new');           // Create user
    $routes->post('users/(:num)', 'UserController::update/$1'); // Update user
    $routes->patch('users/(:num)', 'UserController::update/$1'); // Update user
    $routes->delete('users/(:num)', 'UserController::delete/$1'); // Delete user
    $routes->options('users', static function () {
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');
        return $response;
    });

    // Login route
    $routes->post('login', 'UserController::login');
    $routes->options('login', static function () {
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');
        return $response;
    });
    $routes->put('logout/(:num)', 'UserController::logout/$1');

    //permission
    $routes->resource('permissions', [
        'controller' => 'PermissionController',
    ]);

    //role
    $routes->resource('role', [
        'controller' => 'RoleController',
    ]);

    $routes->resource('subcategories', [
        'controller' => 'SubCategoryController',
        'only' => ['index', 'show', 'create', 'delete'],
    ]);
    $routes->post('subcategories/(:num)', 'SubCategoryController::update/$1');
    $routes->get('subcategoriesBySlug/(:any)', 'SubCategoryController::getBySlug/$1');

    //vendor routes
    $routes->post('vendor/register', 'VendorController::register');
    $routes->post('vendor/activate/(:num)', 'VendorController::activateVendor/$1');
    $routes->get('vendor/activelist', 'VendorController::getActiveVendors');
    $routes->get('vendor/list', 'VendorController::getInactiveVendors');
    $routes->post('vendor/login', 'VendorController::login');
    $routes->post('customer/register', 'CustomerRegisterController::register');
    $routes->get('customer/list/(:num)', 'CustomerRegisterController::show/$1');
    $routes->options('vendor', static function () {
        $response = response();
        $response->setStatusCode(204);
        $response->setHeader('Allow', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');
        return $response;
    });

    //bookings
    $routes->post('booking', 'BookingController::register');
    $routes->get('booking-list', 'BookingController::index');
    $routes->post('booking/activate/(:num)', 'BookingController::activateBooking/$1');
    $routes->get('booking/activelist', 'BookingController::getActiveBookings');
    $routes->get('booking/list', 'BookingController::getInactiveVendors');
    $routes->delete('booking/(:num)', 'BookingController::delete/$1');
    $routes->get('bookinglist/(:num)', 'BookingController::show/$1');

    //product 
    $routes->group('products', function ($routes) {
        $routes->get('/', 'ProductController::index');
        $routes->post('/', 'ProductController::create');
        $routes->get('(:num)', 'ProductController::show/$1');
        $routes->post('(:num)', 'ProductController::update/$1');
        $routes->delete('(:num)', 'ProductController::delete/$1');
    });

    $routes->post('contact/send', 'ContactController::send_mail');

    $routes->group('blog', function ($routes) {
        $routes->post('add', 'BlogController::addBlog');
        $routes->post('edit/(:num)', 'BlogController::editBlog/$1');
        $routes->get('list', 'BlogController::listBlogs');
        $routes->delete('delete/(:num)', 'BlogController::singleDeletedBlog/$1');
    });
});
