<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'document';
    protected $primaryKey = 'doc_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'photo',
        'result',
        'achievement',
        'satsang_verification',
        'create_at',
        'update_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
