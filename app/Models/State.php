<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'ms_state';
    protected $primaryKey = 'state_id';
    public $timestamps = false;
    
    protected $fillable = ['country_id', 'sname'];
    
    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    
    public function districts()
    {
        return $this->hasMany(District::class, 'state_id');
    }
}
