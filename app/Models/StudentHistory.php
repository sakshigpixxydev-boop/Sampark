<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHistory extends Model
{
    protected $table = 'student_history';
    
    protected $fillable = [
        'student_id',
        'from_sembatches_id',
        'to_sembatches_id',
        'status',
        'migrated_by',
        'remarks',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function fromBatch()
    {
        return $this->belongsTo(SemesterBatch::class, 'from_sembatches_id', 'sembatches_id');
    }

    public function toBatch()
    {
        return $this->belongsTo(SemesterBatch::class, 'to_sembatches_id', 'sembatches_id');
    }

    public function executor()
    {
        return $this->belongsTo(SysUser::class, 'migrated_by', 'sysuser_id');
    }
}
