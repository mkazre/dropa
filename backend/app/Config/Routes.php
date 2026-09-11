<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes);

// ---------------------------------------------------------------------
// REST API — RN app, website, kiosk. Token-authenticated except where noted.
// ---------------------------------------------------------------------
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->post('auth/login', 'AuthController::login');

    $routes->group('', ['filter' => 'tokens'], static function ($routes) {
        $routes->post('auth/logout', 'AuthController::logout');
        $routes->post('auth/change-password', 'AuthController::changePassword');

        $routes->get('me', 'AccountController::me');
        $routes->get('properties/mine', 'PropertiesController::mine');
        $routes->get('lockers/availability', 'PropertiesController::lockerAvailability');
        $routes->post('reservations', 'ReservationsController::create');
        $routes->get('reservations/mine', 'ReservationsController::mine');
        $routes->post('reservations/(:num)/cancel', 'ReservationsController::cancel/$1');
        $routes->post('parcels/collect', 'ParcelsController::collect');
        $routes->get('parcels/mine', 'ParcelsController::mine');

        $routes->post('me/push-token', 'AccountController::registerPushToken');

        $routes->get('reservations/(:num)/payment-options', 'PaymentsController::options/$1');
        $routes->post('payments', 'PaymentsController::initiate');
        $routes->post('payments/(:num)/proof', 'PaymentsController::uploadProof/$1');
    });

    // No login required: a courier only ever has a deposit code.
    $routes->post('parcels/deposit', 'ParcelsController::deposit');

    // Browser landing page after an Ozow/PayFast hosted-page redirect.
    $routes->get('payments/return', 'PaymentsController::returnPage');

    // Inbound hardware/payment provider callbacks.
    $routes->post('webhooks/hivebox', 'WebhooksController::hivebox');
    $routes->post('webhooks/ozow', 'WebhooksController::ozow');
    $routes->post('webhooks/payfast', 'WebhooksController::payfast');
});

// ---------------------------------------------------------------------
// Super Admin panel — platform-wide.
// ---------------------------------------------------------------------
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'superadmin'], static function ($routes) {
    $routes->get('/', 'DashboardController::index');

    $routes->get('properties', 'PropertiesController::index');
    $routes->get('properties/new', 'PropertiesController::new');
    $routes->post('properties', 'PropertiesController::create');
    $routes->get('properties/(:num)/edit', 'PropertiesController::edit/$1');
    $routes->post('properties/(:num)', 'PropertiesController::update/$1');
    $routes->post('properties/(:num)/delete', 'PropertiesController::delete/$1');

    $routes->get('gateways', 'PaymentGatewaysController::index');
    $routes->post('gateways', 'PaymentGatewaysController::update');
});

// ---------------------------------------------------------------------
// Body Corporate Admin panel — one property.
// ---------------------------------------------------------------------
$routes->group('manage', ['namespace' => 'App\Controllers\Manage', 'filter' => 'propertyadmin'], static function ($routes) {
    $routes->get('/', 'DashboardController::index');

    $routes->get('tenants', 'TenantsController::index');
    $routes->get('tenants/new', 'TenantsController::new');
    $routes->post('tenants', 'TenantsController::create');
    $routes->post('tenants/(:num)/delete', 'TenantsController::delete/$1');

    $routes->get('parcels', 'ParcelsController::index');

    $routes->get('payments', 'PaymentsController::index');
    $routes->post('payments/(:num)/approve', 'PaymentsController::approve/$1');

    $routes->get('gateways', 'GatewaySettingsController::index');
    $routes->post('gateways', 'GatewaySettingsController::update');
});
