<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountRule extends Model
{
    protected $table = 'discount_rule';
    protected $primaryKey = 'discount_id';
    public $timestamps = false;
    
    protected $fillable = [
        'scope',
        'discount_type',
        'discount_value',
        'applies_to',
        'valid_from',
        'valid_to',
        'active',
        'school_id',
        'student_id',
    ];
    
    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'active' => 'boolean',
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
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('scope', 'SCHOOL')->where('school_id', $schoolId);
    }
    
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('scope', 'STUDENT')->where('student_id', $studentId);
    }
}
