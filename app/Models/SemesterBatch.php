<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterBatch extends Model
{
    protected $table = 'sembatches';
    protected $primaryKey = 'sembatches_id';
    public $timestamps = false;
    
    protected $fillable = [
        'start_date',
        'end_date',
        'capacity',
        'next_sembatch_id',
        'code_prefix',
        'display_name',
        'status',
        'semester',
        'edmingle_course_id',
        'edmingle_batch_id',
        'edmingle_course_name',
        'edmingle_batch_name',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fees' => 'decimal:2',
        'status' => 'integer',
        'semester' => 'integer',
        'next_semester' => 'integer',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    // Relationships
    public function standard()
    {
        return $this->belongsTo(Standard::class, 'std_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_sembatches', 'sembatches_id', 'student_id')
                    ->withPivot('status', 'assigned_at', 'completed_at');
    }
    
    public function feePlans()
    {
        return $this->belongsToMany(FeePlan::class, 'fee_plan_sembatches', 'sembatches_id', 'fee_plan_id');
    }
    
    public function uploadBatches()
    {
        return $this->hasMany(UploadBatch::class, 'sembatches_id');
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 0);
    }
}
