<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrowthCriterion extends Model
{
    protected $table = 'growth_criteria';
    protected $primaryKey = 'criterion_id';

    protected $fillable = [
        'criterion_name',
        'min_score',
        'max_score',
        'status',
        'display_order',
    ];

    protected $casts = [
        'status' => 'boolean',
        'display_order' => 'integer',
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];
}
