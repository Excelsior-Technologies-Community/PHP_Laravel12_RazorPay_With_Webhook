<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');
        $secret = env('RAZORPAY_WEBHOOK_SECRET');

        $generatedSignature = hash_hmac(
            'sha256',
            $request->getContent(),
            $secret
        );

        if ($signature !== $generatedSignature) {
            return response()->json(['error' => 'Invalid Signature'], 400);
        }

        $payload = $request->payload['payment']['entity'];

        Payment::where('order_id', $payload['order_id'])
            ->update([
                'payment_id' => $payload['id'],
                'status' => $payload['status']
            ]);

        return response()->json(['status' => 'Webhook handled']);
    }
}
