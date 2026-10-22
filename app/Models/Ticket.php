<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'school_id',
        'ticket_type',
        'ticket_category_id',
        'title',
        'description',
        'attachment',
        'status',
        'priority',
        'created_by',
        'assigned_to',
    ];

    // Relationships
    public function comments()
    {
        return $this->hasMany(TicketComment::class, 'ticket_id', 'ticket_id');
    }
    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'sysuser_id');
    }

    public function assignee()
    {
        return $this->belongsTo(SysUser::class, 'assigned_to', 'sysuser_id');
    }

    // Scopes for assignment logic
    public function scopeForUser($query, SysUser $user)
    {
        if ($user->hasRole('Admin') || (!$user->hasRole('School') && !$user->hasRole('Initiator') && $user->canAccessModule(10801))) {
            return $query;
        }

        if ($user->hasRole('School')) {
            $schoolIds = $user->getAssignedSchoolIds();
            return $query->whereIn('school_id', $schoolIds);
        }

        if ($user->hasRole('Initiator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            return $query->whereIn('school_id', $schoolIds);
        }

        return $query->where('created_by', $user->sysuser_id);
    }
}
