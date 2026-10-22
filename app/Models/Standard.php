<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    protected $table = 'ms_standard';
    protected $primaryKey = 'std_id';
    public $timestamps = false;
    
    protected $fillable = ['stdname'];
    
    public function students()
    {
        return $this->hasMany(Student::class, 'std_id');
    }
    
    public function feePlans()
    {
        return $this->hasMany(FeePlan::class, 'std_id');
    }
}
