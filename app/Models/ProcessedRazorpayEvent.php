<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedRazorpayEvent extends Model
{
    protected $table = 'processed_razorpay_events';
    protected $primaryKey = 'event_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'payment_id',
        'order_id',
        'event_type',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
