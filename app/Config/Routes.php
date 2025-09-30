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
$routes->setAutoRoute(true);
