
<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Main Routes
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

// Authentication Routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('admin/logout', 'Admin::logout');

// Admin Dashboard Routes
$routes->get('admin/dashboard', 'Admin::dashboard');
$routes->get('admin/users', 'Admin::users');

// Staff Management Routes
$routes->group('admin', function($routes) {
    // Staff listing and management
    $routes->get('staff-management', 'Admin::staffManagement');
    
    // Staff CRUD operations
    $routes->get('add-staff', 'Admin::addStaff');
    $routes->post('add-staff', 'Admin::addStaff');
    $routes->post('staff/create', 'Admin::addStaff');
    $routes->get('staff/api', 'Admin::getStaffAPI');
    
    // Individual staff operations
    $routes->get('edit-staff/(:num)', 'Admin::editStaff/$1');
    $routes->post('edit-staff/(:num)', 'Admin::editStaff/$1');
    $routes->get('delete-staff/(:num)', 'Admin::deleteStaff/$1');
    $routes->get('view-staff/(:num)', 'Admin::viewStaff/$1');

//Users Management
    $routes->get('user-management', 'Admin::userManagement');
    $routes->post('users/saveUser', 'Admin::saveUser');
    $routes->post('users/updateUser', 'Admin::updateUser');
    $routes->get('users/get/(:num)', 'Admin::getUser/$1');
    $routes->get('users/delete/(:num)', 'Admin::deleteUser/$1');


    //Patient Management
    $routes->get('patient-management', 'Admin::patientManagement');
    $routes->post('patients', 'Admin::createPatient');

});

// Doctor Routes
$routes->get('doctor/dashboard', 'Doctor::dashboard');

$routes->setAutoRoute(true);
