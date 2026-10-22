<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialImage extends Model
{
    protected $table = 'material_images';

    protected $fillable = [
        'material_id',
        'image_path',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
