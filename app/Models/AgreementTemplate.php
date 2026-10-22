<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementTemplate extends Model
{
    protected $table = 'agreement_templates';
    protected $primaryKey = 'agreement_template_id';

    protected $fillable = [
        'slug',
        'title',
        'content_en',
        'content_hi',
        'content_gu',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get active template by slug
     */
    public static function getActiveBySlug($slug)
    {
        return self::where('slug', $slug)->where('is_active', true)->first();
    }
}
