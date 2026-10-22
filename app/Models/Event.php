<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'event';
    protected $primaryKey = 'event_id';
    public $timestamps = false; // Using manual timestamps as per project pattern

    protected $fillable = [
        'school_id',
        'title',
        'event_date',
        'notes',
        'photo',
        'feedback',
        'reminder_date',
        'communication_type',
        'send_to',
        'state_id',
        'dist_id',
        'taluka_id',
        'createdby',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'reminder_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'dist_id');
    }

    public function taluka()
    {
        return $this->belongsTo(Taluko::class, 'taluka_id');
    }
}
