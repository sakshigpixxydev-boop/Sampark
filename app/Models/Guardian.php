<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $table = 'guardian';
    protected $primaryKey = 'guardian_id';
    public $timestamps = false;
    
    protected $fillable = [
        'student_id',
        'relation_id',
        'fname',
        'lname',
        'mname',
        'gender',
        'phone',
        'email',
        'education',
        'work_experience',
        'how_to_help',
        'createdby',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    
    public function relation()
    {
        return $this->belongsTo(Relation::class, 'relation_id');
    }
}
