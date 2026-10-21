<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* ------------------------- Public pages ------------------------- */
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('tracking');
});

Route::get('/track', 'TrackingController@index')->name('tracking');
Route::get('/lang/{locale}', 'LocaleController@switch')->name('lang.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', 'AuthController@showLogin')->name('login');
    Route::post('/login', 'AuthController@login')->middleware('throttle:10,1');
});

/* ------------------------- Authenticated users ------------------------- */
Route::middleware('auth')->group(function () {
    Route::post('/logout', 'AuthController@logout')->name('logout');
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');

    // Notifications
    Route::get('/notifications', 'NotificationController@index')->name('notifications.index');
    Route::get('/notifications/{id}/read', 'NotificationController@read')->name('notifications.read');
    Route::post('/notifications/read-all', 'NotificationController@readAll')->name('notifications.readAll');

    // Profile
    Route::get('/profile', 'ProfileController@edit')->name('profile.edit');
    Route::put('/profile', 'ProfileController@update')->name('profile.update');

    // Shipments (route order matters: create before {shipment})
    Route::get('shipments/create', 'ShipmentController@create')->name('shipments.create')->middleware('permission:shipments.create');
    Route::post('shipments', 'ShipmentController@store')->name('shipments.store')->middleware('permission:shipments.create');
    Route::get('shipments', 'ShipmentController@index')->name('shipments.index')->middleware('permission:shipments.view');
    Route::get('shipments/{shipment}', 'ShipmentController@show')->name('shipments.show')->middleware('permission:shipments.view');
    Route::get('shipments/{shipment}/label', 'ShipmentController@label')->name('shipments.label')->middleware('permission:shipments.view');
    Route::get('shipments/{shipment}/edit', 'ShipmentController@edit')->name('shipments.edit')->middleware('permission:shipments.edit');
    Route::put('shipments/{shipment}', 'ShipmentController@update')->name('shipments.update')->middleware('permission:shipments.edit');
    Route::delete('shipments/{shipment}', 'ShipmentController@destroy')->name('shipments.destroy')->middleware('permission:shipments.delete');
    Route::post('shipments/{shipment}/assign', 'ShipmentController@assign')->name('shipments.assign')->middleware('permission:shipments.assign');
    Route::post('shipments/{shipment}/status', 'ShipmentController@updateStatus')->name('shipments.status')->middleware('permission:shipments.status');

    // Customers
    Route::get('customers', 'CustomerController@index')->name('customers.index')->middleware('permission:customers.view');
    Route::get('customers/create', 'CustomerController@create')->name('customers.create')->middleware('permission:customers.manage');
    Route::post('customers', 'CustomerController@store')->name('customers.store')->middleware('permission:customers.manage');
    Route::get('customers/{customer}', 'CustomerController@show')->name('customers.show')->middleware('permission:customers.view');
    Route::get('customers/{customer}/edit', 'CustomerController@edit')->name('customers.edit')->middleware('permission:customers.manage');
    Route::put('customers/{customer}', 'CustomerController@update')->name('customers.update')->middleware('permission:customers.manage');
    Route::delete('customers/{customer}', 'CustomerController@destroy')->name('customers.destroy')->middleware('permission:customers.delete');

    // Invoices
    Route::get('invoices', 'InvoiceController@index')->name('invoices.index')->middleware('permission:invoices.view');
    Route::get('invoices/create', 'InvoiceController@create')->name('invoices.create')->middleware('permission:invoices.create');
    Route::post('invoices', 'InvoiceController@store')->name('invoices.store')->middleware('permission:invoices.create');
    Route::get('invoices/{invoice}', 'InvoiceController@show')->name('invoices.show')->middleware('permission:invoices.view');
    Route::get('invoices/{invoice}/print', 'InvoiceController@printView')->name('invoices.print')->middleware('permission:invoices.view');
    Route::get('invoices/{invoice}/edit', 'InvoiceController@edit')->name('invoices.edit')->middleware('permission:invoices.edit');
    Route::put('invoices/{invoice}', 'InvoiceController@update')->name('invoices.update')->middleware('permission:invoices.edit');
    Route::post('invoices/{invoice}/pay', 'InvoiceController@pay')->name('invoices.pay')->middleware('permission:invoices.pay');
    Route::post('invoices/{invoice}/cancel', 'InvoiceController@cancel')->name('invoices.cancel')->middleware('permission:invoices.pay');
    Route::delete('invoices/{invoice}', 'InvoiceController@destroy')->name('invoices.destroy')->middleware('permission:invoices.delete');

    // Couriers
    Route::get('couriers', 'CourierController@index')->name('couriers.index')->middleware('permission:couriers.view');
    Route::get('couriers/create', 'CourierController@create')->name('couriers.create')->middleware('permission:couriers.manage');
    Route::post('couriers', 'CourierController@store')->name('couriers.store')->middleware('permission:couriers.manage');
    Route::get('couriers/{courier}', 'CourierController@show')->name('couriers.show')->middleware('permission:couriers.view');
    Route::get('couriers/{courier}/edit', 'CourierController@edit')->name('couriers.edit')->middleware('permission:couriers.manage');
    Route::put('couriers/{courier}', 'CourierController@update')->name('couriers.update')->middleware('permission:couriers.manage');
    Route::delete('couriers/{courier}', 'CourierController@destroy')->name('couriers.destroy')->middleware('permission:couriers.manage');

    // Reports
    Route::get('reports', 'ReportController@index')->name('reports.index')->middleware('permission:reports.view');
    Route::get('reports/export', 'ReportController@export')->name('reports.export')->middleware('permission:reports.export');

    // Administration: branches - users - roles and permissions
    Route::middleware('permission:branches.manage')->group(function () {
        Route::resource('branches', 'BranchController')->except(['show']);
    });
    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', 'UserController')->except(['show']);
    });
    Route::middleware('permission:roles.manage')->group(function () {
        Route::resource('roles', 'RoleController')->except(['show']);
    });
});
