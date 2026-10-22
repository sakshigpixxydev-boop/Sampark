<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $table = 'material_requests';

    protected $fillable = [
        'school_id',
        'material_id',
        'quantity',
        'total_amount',
        'delivery_type',
        'status',
        'admin_remarks',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
            'approved' => 'bg-blue-100 text-blue-800 border border-blue-200',
            'dispatched' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
            'completed' => 'bg-green-100 text-green-800 border border-green-200',
            default => 'bg-gray-100 text-gray-800 border border-gray-200',
        };
    }
}
