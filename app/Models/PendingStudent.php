<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingStudent extends Model
{
    protected $table = 'pending_student';
    protected $primaryKey = 'pending_id';
    public $timestamps = false;
    
    protected $fillable = [
        'batch_id',
        'student_data',
        'calculated_fee',
        'validation_errors',
        'is_registered',
    ];
    
    protected $casts = [
        'student_data' => 'array',
        'validation_errors' => 'array',
        'calculated_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'is_registered' => 'boolean',
    ];
    
    // Scopes
    public function scopeNotRegistered($query)
    {
        return $query->where('is_registered', false);
    }

    // Relationships
    public function uploadBatch()
    {
        return $this->belongsTo(UploadBatch::class, 'batch_id');
    }
}
