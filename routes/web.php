<?php

use Illuminate\Support\Facades\Auth;
use App\Services\SemaphoreSmsService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BillsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientPageController;
use App\Http\Controllers\MeterReadingController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth', 'role:client']], function () {
    Route::get('/customer/record', [ClientPageController::class, 'index'])->name('client.index');
});

Route::group(['middleware' => ['auth', 'role:admin,plumber,cashier']], function () {
    // -------------------------- Meter REading ----------------------//
    Route::get('reading-meter/{id}', [MeterReadingController::class, 'readingMeter'])->name('reading.meter');

    // -------------------------- Bills ----------------------//
    Route::get('client/transaction/{id}', [BillsController::class, 'clientTransaction'])->name('transaction.history');

    // -------------------------- sms ----------------------//
    Route::get('/test-sms', function (SemaphoreSmsService $smsService) {
        $smsService->sendSms('09061237968', 'Yo! This is a test from your Laravel 12 app. 💥');
    });

    Route::resource('billing', BillsController::class);
    Route::resource('meter', MeterReadingController::class);
});

Route::group(['middleware' => ['auth', 'role:admin']], function () {

    // -------------------------- Customer ----------------------//
    Route::patch('/customer/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customer.toggleStatus');

    // -------------------------- Staff ----------------------//
    Route::patch('/staff/{id}/toggle-status', [StaffController::class, 'toggleStaffStatus'])->name('staff.toggleStaffStatus');

    // -------------------------- Record ----------------------//
    Route::get('record', [HomeController::class, 'record'])->name('record.index');

    // -------------------------- Pages ----------------------//
    Route::resource('customer', CustomerController::class);
    Route::resource('staff', StaffController::class);
    Route::resource('payment', PaymentController::class);
    Route::resource('expenses', ExpensesController::class);
    Route::resource('groups', GroupController::class);
    Route::resource('category', CategoryController::class);
});
