<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnnouncementQueue extends Model
{
    protected $table = 'student_announcement_queue';

    protected $fillable = [
        'announcement_id',
        'student_id',
        'status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function announcement()
    {
        return $this->belongsTo(StudentAnnouncement::class, 'announcement_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
