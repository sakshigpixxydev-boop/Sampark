<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGrowthRecord extends Model
{
    protected $table = 'student_growth_records';
    protected $primaryKey = 'growth_id';

    protected $fillable = [
        'student_id',
        'school_id',
        'discipline',
        'leadership',
        'communication',
        'teamwork',
        'participation',
        'behaviour',
        'scores',
        'reflections',
        'created_by',
        'entry_date',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'discipline' => 'integer',
        'leadership' => 'integer',
        'communication' => 'integer',
        'teamwork' => 'integer',
        'participation' => 'integer',
        'behaviour' => 'integer',
        'scores' => 'array',
        'reflections' => 'array',
    ];


    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by');
    }

    /**
     * Get the color class for a specific criteria mark based on SANKALP Sixer Framework.
     * 1.0 - 2.9 (Needs Close Support): Soft Saffron / Warm Orange
     * 3.0 - 4.9 (Beginning Growth): Soft Gold / Yellow
     * 5.0 - 6.9 (Growing Steadily): Light Blue
     * 7.0 - 8.4 (Strong Progress): Soft Green
     * 8.5 - 10.0 (Inspiring Growth): Deep Teal
     */
    public static function getColorInfo($marks)
    {
        if ($marks >= 8.5) {
            return [
                'color' => 'Inspiring Growth',
                'bg_class' => 'bg-teal-700',
                'text_class' => 'text-white',
                'hex' => '#0f766e'
            ];
        } elseif ($marks >= 7) {
            return [
                'color' => 'Strong Progress',
                'bg_class' => 'bg-emerald-400',
                'text_class' => 'text-gray-900',
                'hex' => '#34d399'
            ];
        } elseif ($marks >= 5) {
            return [
                'color' => 'Growing Steadily',
                'bg_class' => 'bg-sky-400',
                'text_class' => 'text-gray-900',
                'hex' => '#38bdf8'
            ];
        } elseif ($marks >= 3) {
            return [
                'color' => 'Beginning Growth',
                'bg_class' => 'bg-yellow-400',
                'text_class' => 'text-gray-900',
                'hex' => '#facc15'
            ];
        } else {
            return [
                'color' => 'Needs Support',
                'bg_class' => 'bg-orange-400',
                'text_class' => 'text-white',
                'hex' => '#fb923c'
            ];
        }
    }
}
