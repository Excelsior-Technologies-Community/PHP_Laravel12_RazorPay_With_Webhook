<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\PaymentReceiptController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', [RazorpayController::class, 'index'])->name('payments.index');

Route::get('/payment/{id}/receipt', [PaymentReceiptController::class, 'download'])
    ->name('payment.receipt');

// Create Razorpay Order
Route::post('/create-order', [RazorpayController::class, 'createOrder'])
    ->name('payments.create-order');

// Payment Success Callback
Route::post('/payment-success', [RazorpayController::class, 'paymentSuccess'])
    ->name('payments.success');

// Razorpay Webhook
Route::post('/razorpay-webhook', [RazorpayWebhookController::class, 'handle'])
    ->name('payments.webhook');

// Delete Payment
Route::delete('/payments/{id}', [RazorpayController::class, 'destroy'])
    ->name('payments.destroy');
