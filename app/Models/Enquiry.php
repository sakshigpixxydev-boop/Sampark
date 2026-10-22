<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enquiry extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'enquiries';
    protected $primaryKey = 'enquiry_id';

    protected $fillable = [
        'source',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'whatsapp_no',
        'gender',
        'birthdate',
        'std_id',
        'standard_name',
        'eduboard_id',
        'board_name',
        'medium',
        'stream',
        'school_name',
        'country_id',
        'state_id',
        'dist_id',
        'takula_id',
        'country_name',
        'state_name',
        'district_name',
        'taluka_name',
        'city_village',
        'address',
        'pincode',
        'guardian_name',
        'guardian_contact',
        'guardian_relation',
        'selected_language',
        'message',
        'status',
        'assigned_to',
        'remarks',
        'converted_student_id',
        'contacted_at',
        'ip_address',
        'user_agent',
        'raw_payload',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'raw_payload' => 'array',
        'contacted_at' => 'datetime',
    ];

    public const STATUS_NEW = 'New';
    public const STATUS_CONTACTED = 'Contacted';
    public const STATUS_CONVERTED = 'Converted';
    public const STATUS_CLOSED = 'Closed';

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_CONVERTED,
            self::STATUS_CLOSED,
        ];
    }

    // Relationships

    public function assignee()
    {
        return $this->belongsTo(SysUser::class, 'assigned_to', 'sysuser_id');
    }

    public function standard()
    {
        return $this->belongsTo(Standard::class, 'std_id', 'std_id');
    }

    public function educationalBoard()
    {
        return $this->belongsTo(EducationalBoard::class, 'eduboard_id');
    }

    public function taluka()
    {
        return $this->belongsTo(Taluko::class, 'takula_id', 'takula_id');
    }

    public function convertedStudent()
    {
        return $this->belongsTo(Student::class, 'converted_student_id', 'student_id');
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }
}
