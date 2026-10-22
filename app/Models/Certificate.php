<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $table = 'certificates';
    protected $primaryKey = 'certificate_id';

    protected $fillable = [
        'type',
        'reference_id',
        'sembatches_id',
        'certificate_no',
        'recipient_name',
        'start_date',
        'end_date',
        'generated_date',
        'created_by'
    ];

    protected $casts = [
        'generated_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'sysuser_id');
    }

    // Dynamic relationship based on type
    public function getReferenceAttribute()
    {
        switch ($this->type) {
            case 'STUDENT':
                return Student::find($this->reference_id);
            case 'EVENT':
                return Event::find($this->reference_id);
            case 'PROGRAM':
                return SemesterBatch::find($this->reference_id);
            default:
                return null;
        }
    }

    public function batch()
    {
        return $this->belongsTo(SemesterBatch::class, 'sembatches_id', 'sembatches_id');
    }
}
