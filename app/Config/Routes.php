<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');
$routes->get('contact', 'Home::contact');

//Authentication
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// Admin Routes
$routes->get('admin/dashboard', 'Admin::dashboard');
$routes->get('admin/staff-management', 'Admin::staffManagement');

$routes->get('admin/users', 'Admin::users');
$routes->setAutoRoute(true);
