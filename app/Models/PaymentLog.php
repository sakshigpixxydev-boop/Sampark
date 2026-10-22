<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\Auditable;

class PaymentLog extends Model
{
    use Auditable;

    protected $table = 'paymentlog';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;
    
    protected $fillable = [
        'paymode_id',
        'processdatetime',
        'amount',
        'status',
        'txnid',
        'orgresponse',
        'discount',
        'invoice_id',
    ];
    
    protected $casts = [
        'processdatetime' => 'datetime',
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
    ];
    
    public function getRouteKeyName()
    {
        return 'payment_id';
    }
    
    // Relationships
    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class, 'paymode_id');
    }
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    
    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
