<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'name',
        'description',
        'price',
        'discounted_price',
        'discount_start_date',
        'discount_end_date',
        'material_type',
        'inactive',
        'filters',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'discount_start_date' => 'date',
        'discount_end_date' => 'date',
        'material_type' => 'integer',
        'inactive' => 'boolean',
        'filters' => 'array',
    ];

    public function images()
    {
        return $this->hasMany(MaterialImage::class, 'material_id');
    }

    public function requests()
    {
        return $this->hasMany(MaterialRequest::class, 'material_id');
    }

    public function getTypeNameAttribute()
    {
        return $this->material_type == 0 ? 'Digital' : 'Printable';
    }

    public function getActivePriceAttribute()
    {
        if ($this->is_active_discount) {
            return $this->discounted_price;
        }
        return $this->price;
    }

    public function getIsActiveDiscountAttribute()
    {
        if (!$this->discounted_price) {
            return false;
        }

        $now = now()->startOfDay();
        $start = $this->discount_start_date ? $this->discount_start_date->startOfDay() : null;
        $end = $this->discount_end_date ? $this->discount_end_date->endOfDay() : null;

        if ($start && $now->lt($start)) return false;
        if ($end && $now->gt($end)) return false;

        return true;
    }
}
