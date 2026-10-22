<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'ms_country';
    protected $primaryKey = 'country_id';
    public $timestamps = false;
    
    protected $fillable = ['cname'];
    
    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    public function states()
    {
        return $this->hasMany(State::class, 'country_id');
    }
}
