<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InitiatorPayment extends Model
{
    protected $table = 'initiator_payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'initiator_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function initiator()
    {
        return $this->belongsTo(SysUser::class, 'initiator_id', 'sysuser_id');
    }

    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'sysuser_id');
    }
}
