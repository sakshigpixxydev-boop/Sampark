<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolType extends Model
{
    protected $table = 'schooltype';
    protected $primaryKey = 'schooltype_id';
    public $timestamps = false;
    
    protected $fillable = ['schooltypename'];
    
    public function schools()
    {
        return $this->hasMany(School::class, 'schooltype_id');
    }
}
