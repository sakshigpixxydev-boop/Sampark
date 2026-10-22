<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'ms_dist';
    protected $primaryKey = 'dist_id';
    public $timestamps = false;
    
    protected $fillable = ['state_id', 'dname'];
    
    protected $casts = [
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
    
    public function talukos()
    {
        return $this->hasMany(Taluko::class, 'dist_id');
    }
}
