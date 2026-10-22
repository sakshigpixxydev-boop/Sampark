<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnnouncement extends Model
{
    protected $table = 'student_announcements';

    protected $fillable = [
        'template_id',
        'filters',
        'subject',
        'message',
        'communication_type',
        'status',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(GlobalTemplate::class, 'template_id');
    }

    public function queue()
    {
        return $this->hasMany(StudentAnnouncementQueue::class, 'announcement_id');
    }
}
