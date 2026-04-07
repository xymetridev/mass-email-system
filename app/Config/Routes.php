<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

service('auth')->routes($routes);

$routes->group('api', ['filter' => 'session'], static function (RouteCollection $routes): void {
    // Admin-only endpoints (all users' data)
    $routes->group('admin', ['filter' => 'group:admin'], static function (RouteCollection $routes): void {
        $routes->get('users', 'Admin\\UsersController::index');
        $routes->get('users/(:num)', 'Admin\\UsersController::show/$1');
    });

    // Owner-scoped endpoints (admin can also pass due to ownership helper)
    $routes->get('users/(:num)/profile', 'User\\ProfileController::show/$1', ['filter' => 'ownership:2']);
    $routes->put('users/(:num)/profile', 'User\\ProfileController::update/$1', ['filter' => 'ownership:2']);
});
