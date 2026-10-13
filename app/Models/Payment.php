<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_id',
        'status',
        'amount',
        'currency',
        'customer_name',
        'customer_email',
        'customer_contact',
        'notes',
        'refund_id',
        'refunded_amount',
        'refund_status',
        'refund_reason',
        'failure_reason',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    public static function checkAndAddColumns()
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'currency')) {
                    $table->string('currency', 10)->default('INR')->after('amount');
                }
                if (!Schema::hasColumn('payments', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('currency');
                }
                if (!Schema::hasColumn('payments', 'customer_email')) {
                    $table->string('customer_email')->nullable()->after('customer_name');
                }
                if (!Schema::hasColumn('payments', 'customer_contact')) {
                    $table->string('customer_contact')->nullable()->after('customer_email');
                }
                if (!Schema::hasColumn('payments', 'notes')) {
                    $table->text('notes')->nullable()->after('customer_contact');
                }
                if (!Schema::hasColumn('payments', 'refund_id')) {
                    $table->string('refund_id')->nullable()->after('status');
                }
                if (!Schema::hasColumn('payments', 'refunded_amount')) {
                    $table->decimal('refunded_amount', 10, 2)->default(0)->after('refund_id');
                }
                if (!Schema::hasColumn('payments', 'refund_status')) {
                    $table->string('refund_status')->nullable()->after('refunded_amount');
                }
                if (!Schema::hasColumn('payments', 'refund_reason')) {
                    $table->text('refund_reason')->nullable()->after('refund_status');
                }
                if (!Schema::hasColumn('payments', 'failure_reason')) {
                    $table->text('failure_reason')->nullable()->after('refund_reason');
                }
            });
        }
    }
}