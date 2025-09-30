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
$routes->setAutoRoute(true);

// Accountant
$routes->group('accountant', static function ($routes) {
    // Pages
    $routes->get('/', 'Accountant::dashboard');
    $routes->get('dashboard', 'Accountant::dashboard');
    $routes->get('billing', 'Accountant::billing');
    $routes->get('payments', 'Accountant::payments');
    $routes->get('insurance', 'Accountant::insurance');

    // Actions
    $routes->post('billing', 'Accountant::createInvoice');
    $routes->post('payments', 'Accountant::processPayment');
    $routes->post('insurance', 'Accountant::submitClaim');
});
