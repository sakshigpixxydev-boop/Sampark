<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InitiatorFinancialConfig extends Model
{
    const TYPE_PER_STUDENT = 0;
    const TYPE_SLAB_BASED = 1;

    protected $table = 'initiator_financial_configs';

    protected $fillable = [
        'name',
        'student_calculation_type',
        'incentive_percentage',
        'plan_based_percentage',
        'new_school_registration_commission',
        'school_count_base',
        'school_count_slab_amount',
        'per_student_commission_first_semester',
        'per_student_commission_repeat_semester',
    ];

    public function users()
    {
        return $this->belongsToMany(SysUser::class, 'initiator_financial_config_user', 'config_id', 'sysuser_id');
    }

    public function slabs()
    {
        return $this->hasMany(InitiatorFinancialSlab::class, 'config_id');
    }
}
