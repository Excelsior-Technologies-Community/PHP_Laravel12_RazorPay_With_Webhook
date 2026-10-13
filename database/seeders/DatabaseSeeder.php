<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Payment;
use App\Models\WebhookLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Payment::checkAndAddColumns();
        WebhookLog::checkAndCreateTable();

        // Sample rich payment transactions
        $samplePayments = [
            [
                'order_id' => 'order_Lrv' . strtoupper(Str::random(10)),
                'payment_id' => 'pay_' . strtoupper(Str::random(12)),
                'amount' => 1500.00,
                'status' => Payment::STATUS_SUCCESS,
                'currency' => 'INR',
                'customer_name' => 'Amit Shah',
                'customer_email' => 'amit@example.com',
                'customer_contact' => '9825123456',
                'notes' => 'Annual SaaS Subscription',
            ],
            [
                'order_id' => 'order_Lrv' . strtoupper(Str::random(10)),
                'payment_id' => 'pay_' . strtoupper(Str::random(12)),
                'amount' => 500.00,
                'status' => Payment::STATUS_REFUNDED,
                'currency' => 'INR',
                'customer_name' => 'Priya Patel',
                'customer_email' => 'priya@example.com',
                'customer_contact' => '9879012345',
                'notes' => 'T-Shirt Purchase',
                'refund_id' => 'rfnd_' . strtoupper(Str::random(10)),
                'refunded_amount' => 500.00,
                'refund_status' => 'processed',
                'refund_reason' => 'Defective item returned',
            ],
            [
                'order_id' => 'order_Lrv' . strtoupper(Str::random(10)),
                'payment_id' => 'pay_' . strtoupper(Str::random(12)),
                'amount' => 2500.00,
                'status' => Payment::STATUS_PARTIALLY_REFUNDED,
                'currency' => 'INR',
                'customer_name' => 'Karan Dave',
                'customer_email' => 'karan@example.com',
                'customer_contact' => '9724098765',
                'notes' => 'Course Bundle Enrollment',
                'refund_id' => 'rfnd_' . strtoupper(Str::random(10)),
                'refunded_amount' => 1000.00,
                'refund_status' => 'processed',
                'refund_reason' => 'Partial module discount adjustment',
            ],
            [
                'order_id' => 'order_Lrv' . strtoupper(Str::random(10)),
                'payment_id' => 'pay_' . strtoupper(Str::random(12)),
                'amount' => 750.00,
                'status' => Payment::STATUS_FAILED,
                'currency' => 'INR',
                'customer_name' => 'Vikram Rathore',
                'customer_email' => 'vikram@example.com',
                'customer_contact' => '9909054321',
                'notes' => 'Cloud Backup Add-on',
                'failure_reason' => 'Bank gateway connection timeout',
            ],
            [
                'order_id' => 'order_Lrv' . strtoupper(Str::random(10)),
                'payment_id' => null,
                'amount' => 5000.00,
                'status' => Payment::STATUS_PENDING,
                'currency' => 'INR',
                'customer_name' => 'Sneha Desai',
                'customer_email' => 'sneha@example.com',
                'customer_contact' => '9898112233',
                'notes' => 'Consulting Session',
            ],
        ];

        foreach ($samplePayments as $p) {
            Payment::firstOrCreate(
                ['order_id' => $p['order_id']],
                $p
            );
        }

        // Sample Webhook Audit Logs
        $sampleLogs = [
            [
                'event' => 'payment.captured',
                'event_id' => 'evt_' . uniqid(),
                'order_id' => $samplePayments[0]['order_id'],
                'payment_id' => $samplePayments[0]['payment_id'],
                'status' => 'verified',
                'payload' => [
                    'event' => 'payment.captured',
                    'entity' => 'event',
                    'payload' => [
                        'payment' => [
                            'entity' => [
                                'id' => $samplePayments[0]['payment_id'],
                                'amount' => 150000,
                                'currency' => 'INR',
                                'status' => 'captured',
                                'method' => 'upi',
                            ]
                        ]
                    ]
                ],
                'headers' => ['X-Razorpay-Signature' => 'sha256_mock_sig_123', 'Content-Type' => 'application/json'],
            ],
            [
                'event' => 'refund.processed',
                'event_id' => 'evt_' . uniqid(),
                'order_id' => $samplePayments[1]['order_id'],
                'payment_id' => $samplePayments[1]['payment_id'],
                'status' => 'verified',
                'payload' => [
                    'event' => 'refund.processed',
                    'entity' => 'event',
                    'payload' => [
                        'refund' => [
                            'entity' => [
                                'id' => $samplePayments[1]['refund_id'],
                                'payment_id' => $samplePayments[1]['payment_id'],
                                'amount' => 50000,
                                'status' => 'processed'
                            ]
                        ]
                    ]
                ],
                'headers' => ['X-Razorpay-Signature' => 'sha256_mock_sig_456', 'Content-Type' => 'application/json'],
            ],
            [
                'event' => 'payment.failed',
                'event_id' => 'evt_' . uniqid(),
                'order_id' => $samplePayments[3]['order_id'],
                'payment_id' => $samplePayments[3]['payment_id'],
                'status' => 'verified',
                'payload' => [
                    'event' => 'payment.failed',
                    'entity' => 'event',
                    'payload' => [
                        'payment' => [
                            'entity' => [
                                'id' => $samplePayments[3]['payment_id'],
                                'amount' => 75000,
                                'error_description' => 'Bank gateway connection timeout',
                                'status' => 'failed'
                            ]
                        ]
                    ]
                ],
                'headers' => ['X-Razorpay-Signature' => 'sha256_mock_sig_789', 'Content-Type' => 'application/json'],
            ]
        ];

        foreach ($sampleLogs as $log) {
            WebhookLog::firstOrCreate(
                ['event_id' => $log['event_id']],
                $log
            );
        }
    }
}
