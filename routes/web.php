<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MeterReadingController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    // -------------------------- Meter REading ----------------------//
    Route::get('reading-meter/{id}', [MeterReadingController::class, 'readingMeter'])->name('reading.meter');

    // -------------------------- Bills ----------------------//
    Route::get('client/transaction/{id}', [BillsController::class, 'clientTransaction'])->name('transaction.history');

    // -------------------------- Customer ----------------------//
    Route::patch('/customer/{id}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customer.toggleStatus');



    // -------------------------- Pages ----------------------//
    Route::resource('customer', CustomerController::class);
    Route::resource('meter', MeterReadingController::class);
    Route::resource('billing', BillsController::class);
    Route::resource('payment', PaymentController::class);
});
