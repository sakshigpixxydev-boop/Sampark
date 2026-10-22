<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalBoard extends Model
{
    protected $table = 'ms_eductionalboard';
    protected $primaryKey = 'eduboard_id';
    public $timestamps = false;
    
    protected $fillable = ['boardname'];
    
    public function schools()
    {
        return $this->hasMany(School::class, 'eduboard_id');
    }
    
    public function students()
    {
        return $this->hasMany(Student::class, 'eduboard_id');
    }
}
