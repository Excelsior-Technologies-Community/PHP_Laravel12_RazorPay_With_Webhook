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

// Download PDF Receipt
Route::get('/payment/{id}/receipt', [PaymentReceiptController::class, 'download'])
    ->name('payment.receipt');

// Create Dynamic Razorpay Order
Route::post('/create-order', [RazorpayController::class, 'createOrder'])
    ->name('payments.create-order');

// Payment Success Callback
Route::post('/payment-success', [RazorpayController::class, 'paymentSuccess'])
    ->name('payments.success');

// 1-Click Refund Route (Full / Partial)
Route::post('/payments/{id}/refund', [RazorpayController::class, 'refund'])
    ->name('payments.refund');

// Razorpay Webhook Handler
Route::post('/razorpay-webhook', [RazorpayWebhookController::class, 'handle'])
    ->name('payments.webhook');

// Webhook Simulation Route
Route::post('/webhook/simulate', [RazorpayWebhookController::class, 'simulate'])
    ->name('webhook.simulate');

// Clear Webhook Audit Logs
Route::post('/webhook/clear', [RazorpayController::class, 'clearWebhookLogs'])
    ->name('webhook.clear');

// Delete Payment
Route::delete('/payments/{id}', [RazorpayController::class, 'destroy'])
    ->name('payments.destroy');
