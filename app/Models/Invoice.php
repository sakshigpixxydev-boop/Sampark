<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $primaryKey = 'invoice_id';
    public $timestamps = false;
    
    protected $fillable = [
        'invoice_date',
        'due_date',
        'notes',
        'total_before_discount',
        'discount_total',
        'total_due',
        'status',
        'school_id',
        'student_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_before_discount' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total_due' => 'decimal:2',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }
    
    public function payments()
    {
        return $this->hasMany(PaymentLog::class, 'invoice_id');
    }
    
    // Methods
    public function calculateTotal()
    {
        $subtotal = $this->items->sum('line_amount');
        $this->total_before_discount = $subtotal;
        
        if ($this->student) {
            $this->discount_total = $this->student->applyDiscounts($subtotal);
        }
        
        $this->total_due = $this->total_before_discount - $this->discount_total;
        $this->save();
        
        return $this->total_due;
    }
    
    public function applyDiscount(DiscountRule $discount)
    {
        if ($discount->discount_type === 'FLAT') {
            $this->discount_total += $discount->discount_value;
        } else { // PERCENT
            $this->discount_total += ($this->total_before_discount * $discount->discount_value / 100);
        }
        
        $this->total_due = $this->total_before_discount - $this->discount_total;
        $this->save();
    }
    
    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
