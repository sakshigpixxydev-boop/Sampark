<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    //
    protected $fillable = [
        'student_id',
        'title',
        'content',
        'status',
        'reviewer_id',
        'review_comment',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(SysUser::class, 'reviewer_id', 'sysuser_id');
    }
}
