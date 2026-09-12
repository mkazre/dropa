<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('post-login', 'PostLoginController::index');

service('auth')->routes($routes);

// ---------------------------------------------------------------------
// REST API — RN app, website, kiosk. Token-authenticated except where noted.
// ---------------------------------------------------------------------
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->post('auth/login', 'AuthController::login', ['filter' => 'throttle:10,300']);

    $routes->group('', ['filter' => 'tokens'], static function ($routes) {
        $routes->post('auth/logout', 'AuthController::logout');
        $routes->post('auth/change-password', 'AuthController::changePassword');

        $routes->get('me', 'AccountController::me');
        $routes->get('properties/mine', 'PropertiesController::mine');
        $routes->get('properties/mine/residents', 'PropertiesController::residents');
        $routes->get('lockers/availability', 'PropertiesController::lockerAvailability');
        $routes->post('lockers/recommend-size', 'PropertiesController::recommendSize');

        $routes->get('public-sites', 'PublicSitesController::index');
        $routes->get('public-sites/(:num)/availability', 'PublicSitesController::availability/$1');
        $routes->post('reservations', 'ReservationsController::create');
        $routes->get('reservations/mine', 'ReservationsController::mine');
        $routes->post('reservations/(:num)/cancel', 'ReservationsController::cancel/$1');
        $routes->get('parcels/mine', 'ParcelsController::mine');
        $routes->post('parcels/(:num)/delegate', 'ParcelsController::createDelegateCode/$1');

        $routes->post('maintenance-tickets', 'MaintenanceController::create');
        $routes->get('maintenance-tickets/mine', 'MaintenanceController::mine');

        $routes->post('pre-alerts', 'PreAlertsController::create');
        $routes->get('pre-alerts/mine', 'PreAlertsController::mine');
        $routes->post('pre-alerts/(:num)/cancel', 'PreAlertsController::cancel/$1');

        $routes->post('me/push-token', 'AccountController::registerPushToken');

        $routes->get('reservations/(:num)/payment-options', 'PaymentsController::options/$1');
        $routes->post('payments', 'PaymentsController::initiate');
        $routes->post('payments/(:num)/proof', 'PaymentsController::uploadProof/$1');
    });

    // No login required: a courier only ever has a deposit code, and
    // collection is authorized by knowledge of the PIN/QR/delegate code
    // itself (not by being logged in) — this is what lets a family
    // member/helper collect on a tenant's behalf, or a future kiosk/website
    // collect flow work without an account.
    $routes->post('parcels/deposit', 'ParcelsController::deposit', ['filter' => 'throttle:20,60']);
    $routes->post('parcels/collect', 'ParcelsController::collect', ['filter' => 'throttle:10,60']);

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
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => ['superadmin', 'nocache']], static function ($routes) {
    $routes->get('/', 'DashboardController::index');

    $routes->get('properties', 'PropertiesController::index');
    $routes->get('properties/new', 'PropertiesController::new');
    $routes->post('properties', 'PropertiesController::create');
    $routes->get('properties/(:num)/edit', 'PropertiesController::edit/$1');
    $routes->post('properties/(:num)', 'PropertiesController::update/$1');
    $routes->post('properties/(:num)/delete', 'PropertiesController::delete/$1');

    $routes->get('gateways', 'PaymentGatewaysController::index');
    $routes->post('gateways', 'PaymentGatewaysController::update');

    $routes->get('pricing', 'PricingController::index');
    $routes->post('pricing', 'PricingController::update');

    $routes->get('audit-log', 'AuditLogController::index');

    $routes->get('hardware', 'HardwareController::index');

    $routes->get('billing', 'BillingController::index');
    $routes->post('billing/(:num)/fee', 'BillingController::setFee/$1');
    $routes->post('billing/generate', 'BillingController::generateInvoices');
    $routes->post('billing/(:num)/paid', 'BillingController::markPaid/$1');

    $routes->get('broadcast', 'BroadcastController::index');
    $routes->post('broadcast/send', 'BroadcastController::send');
});

// ---------------------------------------------------------------------
// Body Corporate Admin panel — one property.
// ---------------------------------------------------------------------
$routes->group('manage', ['namespace' => 'App\Controllers\Manage', 'filter' => ['propertyadmin', 'nocache']], static function ($routes) {
    // Staff (kiosk-assist) can reach these — helping a resident find a
    // parcel's status or logging a fault doesn't require owner-level access.
    $routes->get('/', 'DashboardController::index');
    $routes->get('parcels', 'ParcelsController::index');
    $routes->get('maintenance', 'MaintenanceController::index');
    $routes->post('maintenance/(:num)/status', 'MaintenanceController::updateStatus/$1');

    // Everything else — people, money, settings — is Body Corporate only.
    $routes->group('', ['filter' => 'propertyowner'], static function ($routes) {
        $routes->get('tenants', 'TenantsController::index');
        $routes->get('tenants/new', 'TenantsController::new');
        $routes->post('tenants', 'TenantsController::create');
        $routes->post('tenants/(:num)/delete', 'TenantsController::delete/$1');
        $routes->get('tenants/import', 'TenantsController::importForm');
        $routes->post('tenants/import', 'TenantsController::import');

        $routes->get('staff', 'StaffController::index');
        $routes->get('staff/new', 'StaffController::new');
        $routes->post('staff', 'StaffController::create');
        $routes->post('staff/(:num)/delete', 'StaffController::delete/$1');

        $routes->get('payments', 'PaymentsController::index');
        $routes->post('payments/(:num)/approve', 'PaymentsController::approve/$1');

        $routes->get('gateways', 'GatewaySettingsController::index');
        $routes->post('gateways', 'GatewaySettingsController::update');

        $routes->get('pricing', 'PricingController::index');
        $routes->post('pricing', 'PricingController::update');

        $routes->get('branding', 'BrandingController::edit');
        $routes->post('branding', 'BrandingController::update');

        $routes->get('broadcast', 'BroadcastController::index');
        $routes->post('broadcast/send', 'BroadcastController::send');
    });
});
