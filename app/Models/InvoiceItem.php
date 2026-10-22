<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_item';
    protected $primaryKey = 'invoice_item_id';
    public $timestamps = false;
    
    protected $fillable = [
        'description',
        'qty',
        'unit_amount',
        'line_amount',
        'invoice_id',
        'fee_plan_id',
    ];
    
    protected $casts = [
        'qty' => 'decimal:2',
        'unit_amount' => 'decimal:2',
        'line_amount' => 'decimal:2',
    ];
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    
    public function feePlan()
    {
        return $this->belongsTo(FeePlan::class, 'fee_plan_id');
    }
}
