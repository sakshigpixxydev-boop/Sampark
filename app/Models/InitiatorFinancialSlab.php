<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InitiatorFinancialSlab extends Model
{
protected $table = 'initiator_financial_slabs';

    protected $fillable = [
        'config_id',
        'min_students',
        'max_students',
        'commission_type',
        'commission_value',
        'commission_percentage',
        'fixed_amount',
    ];

    public function config()
    {
        return $this->belongsTo(InitiatorFinancialConfig::class, 'config_id');
    }
}
