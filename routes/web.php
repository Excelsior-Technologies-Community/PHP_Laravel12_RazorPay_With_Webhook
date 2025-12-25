<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RazorpayWebhookController;

Route::get('/', [RazorpayController::class, 'index']);
Route::post('/create-order', [RazorpayController::class, 'createOrder']);
Route::post('/payment-success', [RazorpayController::class, 'paymentSuccess']);

Route::post('/razorpay-webhook', [RazorpayWebhookController::class, 'handle']);
