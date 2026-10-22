<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePlan extends Model
{
    protected $table = 'fee_plan';
    protected $primaryKey = 'fee_plan_id';
    public $timestamps = false;
    
    protected $fillable = [
        'fee_name',
        'amount',
        'strike_through_amount',
        'valid_from',
        'valid_to',
        'sembatches_id',
        'is_active_on_form',
        'school_id',
        'plan_auto_close_date',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'strike_through_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'plan_auto_close_date' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    // Relationships
    public function semesterBatch()
    {
        return $this->belongsTo(SemesterBatch::class, 'sembatches_id');
    }
    
    public function semesterBatches()
    {
        return $this->belongsToMany(SemesterBatch::class, 'fee_plan_sembatches', 'fee_plan_id', 'sembatches_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function users()
    {
        return $this->belongsToMany(SysUser::class, 'fee_plan_user', 'fee_plan_id', 'sysuser_id');
    }
}
