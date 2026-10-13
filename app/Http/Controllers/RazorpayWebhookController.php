<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        WebhookLog::checkAndCreateTable();

        $signature = $request->header('X-Razorpay-Signature');
        $secret = env('RAZORPAY_WEBHOOK_SECRET');
        $rawPayload = $request->getContent();

        $generatedSignature = hash_hmac(
            'sha256',
            $rawPayload,
            $secret ?? ''
        );

        $isValidSignature = ($signature && $generatedSignature && hash_equals($signature, $generatedSignature));

        // In test/sandbox environments, log and handle if payload is present
        $data = json_decode($rawPayload, true) ?: $request->all();
        $event = $data['event'] ?? 'unknown.event';
        $eventId = $data['id'] ?? null;
        
        $paymentEntity = $data['payload']['payment']['entity'] ?? null;
        $refundEntity = $data['payload']['refund']['entity'] ?? null;
        $orderId = $paymentEntity['order_id'] ?? ($data['payload']['order']['entity']['id'] ?? null);
        $paymentId = $paymentEntity['id'] ?? ($refundEntity['payment_id'] ?? null);

        // Record Webhook Audit Log
        $log = WebhookLog::create([
            'event' => $event,
            'event_id' => $eventId,
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'status' => $isValidSignature ? 'verified' : 'received_unverified',
            'payload' => $data,
            'headers' => [
                'X-Razorpay-Signature' => $signature,
                'Content-Type' => $request->header('Content-Type'),
                'User-Agent' => $request->header('User-Agent'),
            ],
        ]);

        if (!$isValidSignature && !empty($secret)) {
            Log::warning('Webhook signature verification failed for event: ' . $event);
            return response()->json(['error' => 'Invalid Signature', 'log_id' => $log->id], 400);
        }

        // Process various Razorpay webhook events
        switch ($event) {
            case 'payment.captured':
            case 'order.paid':
                if ($orderId) {
                    Payment::where('order_id', $orderId)->update([
                        'payment_id' => $paymentId,
                        'status' => Payment::STATUS_SUCCESS,
                    ]);
                }
                break;

            case 'payment.failed':
                $failureReason = $paymentEntity['error_description'] ?? ($paymentEntity['error_reason'] ?? 'Payment failed at gateway');
                if ($orderId) {
                    Payment::where('order_id', $orderId)->update([
                        'payment_id' => $paymentId,
                        'status' => Payment::STATUS_FAILED,
                        'failure_reason' => $failureReason,
                    ]);
                }
                break;

            case 'refund.processed':
            case 'refund.created':
                if ($paymentId) {
                    $refundedAmountPaise = $refundEntity['amount'] ?? 0;
                    $refundedAmount = $refundedAmountPaise / 100;
                    $refundId = $refundEntity['id'] ?? null;

                    $p = Payment::where('payment_id', $paymentId)->first();
                    if ($p) {
                        $newRefundTotal = ($p->refunded_amount ?? 0) + $refundedAmount;
                        $status = ($newRefundTotal >= $p->amount) ? Payment::STATUS_REFUNDED : Payment::STATUS_PARTIALLY_REFUNDED;
                        $p->update([
                            'refund_id' => $refundId ?? $p->refund_id,
                            'refunded_amount' => $newRefundTotal,
                            'refund_status' => 'processed',
                            'status' => $status,
                        ]);
                    }
                }
                break;

            default:
                Log::info("Webhook event received: {$event}");
                break;
        }

        return response()->json([
            'status' => 'Webhook processed successfully',
            'event' => $event,
            'log_id' => $log->id
        ]);
    }

    /**
     * Test / Simulate Webhook Event for testing
     */
    public function simulate(Request $request)
    {
        WebhookLog::checkAndCreateTable();

        $event = $request->input('event', 'payment.captured');
        $orderId = $request->input('order_id', 'order_sim_' . rand(1000, 9999));
        $paymentId = $request->input('payment_id', 'pay_sim_' . rand(1000, 9999));
        $reason = $request->input('failure_reason', 'Insufficient bank balance');

        $payload = [
            'entity' => 'event',
            'account_id' => 'acc_simulated',
            'event' => $event,
            'contains' => ['payment'],
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => $orderId,
                        'amount' => 50000,
                        'currency' => 'INR',
                        'status' => ($event === 'payment.captured') ? 'captured' : 'failed',
                        'error_description' => ($event === 'payment.failed') ? $reason : null,
                    ]
                ]
            ],
            'created_at' => time()
        ];

        WebhookLog::create([
            'event' => $event,
            'event_id' => 'evt_' . uniqid(),
            'order_id' => $orderId,
            'payment_id' => $paymentId,
            'status' => 'simulated',
            'payload' => $payload,
            'headers' => ['Simulation' => 'true', 'Source' => 'Webhook Radar Panel'],
        ]);

        // Also update local payment status if matching
        if ($event === 'payment.captured') {
            Payment::where('order_id', $orderId)->update([
                'payment_id' => $paymentId,
                'status' => Payment::STATUS_SUCCESS
            ]);
        } elseif ($event === 'payment.failed') {
            Payment::where('order_id', $orderId)->update([
                'payment_id' => $paymentId,
                'status' => Payment::STATUS_FAILED,
                'failure_reason' => $reason
            ]);
        }

        return redirect('/#webhook-section')->with('success', "Simulated webhook event '{$event}' recorded successfully!");
    }
}
