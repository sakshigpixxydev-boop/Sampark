<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchInvoice extends Model
{
    protected $table = 'batch_invoices';
    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'batch_id',
        'school_id',
        'initiator_id',
        'invoice_number',
        'total_amount',
        'invoice_date',
        'exported',
        'exported_at',
        'exported_by',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'exported_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'exported' => 'boolean',
    ];

    // Relationships
    public function batch()
    {
        return $this->belongsTo(UploadBatch::class, 'batch_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function initiator()
    {
        return $this->belongsTo(SysUser::class, 'initiator_id');
    }

    public function exporter()
    {
        return $this->belongsTo(SysUser::class, 'exported_by');
    }

    // Methods

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');

        // Get last invoice number for today
        $lastInvoice = self::where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Mark invoice as exported
     */
    public function markAsExported($userId)
    {
        $this->update([
            'exported' => true,
            'exported_at' => now(),
            'exported_by' => $userId,
        ]);
    }

    // Scopes
    public function scopeNotExported($query)
    {
        return $query->where('exported', false);
    }

    public function scopeExported($query)
    {
        return $query->where('exported', true);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    public function scopeBySchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeByInitiator($query, $initiatorId)
    {
        return $query->where('initiator_id', $initiatorId);
    }

    // Boot method to auto-generate invoice number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }

            if (!$invoice->invoice_date) {
                $invoice->invoice_date = now()->toDateString();
            }
        });
    }
}
