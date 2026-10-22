<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalTemplate extends Model
{
    protected $table = 'global_templates';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'type',
        'filters',
        'is_active',
    ];

    protected $casts = [
        'filters' => 'array',
    ];

    public function announcements()
    {
        return $this->hasMany(StudentAnnouncement::class, 'template_id');
    }
}
