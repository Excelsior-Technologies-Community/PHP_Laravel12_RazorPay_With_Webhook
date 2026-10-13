<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class WebhookLog extends Model
{
    protected $fillable = [
        'event',
        'event_id',
        'order_id',
        'payment_id',
        'status',
        'payload',
        'headers',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
    ];

    public static function checkAndCreateTable()
    {
        if (!Schema::hasTable('webhook_logs')) {
            Schema::create('webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->string('event')->nullable();
                $table->string('event_id')->nullable();
                $table->string('order_id')->nullable();
                $table->string('payment_id')->nullable();
                $table->string('status')->default('received');
                $table->longText('payload')->nullable();
                $table->longText('headers')->nullable();
                $table->timestamps();
            });
        }
    }
}
