<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taluko extends Model
{
    protected $table = 'ms_taluko';
    protected $primaryKey = 'takula_id';
    public $timestamps = false;
    
    protected $fillable = ['dist_id', 'tname'];
    
    public function district()
    {
        return $this->belongsTo(District::class, 'dist_id');
    }
    
    public function schools()
    {
        return $this->hasMany(School::class, 'takula_id');
    }
    
    public function students()
    {
        return $this->hasMany(Student::class, 'takula_id');
    }
}
