<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentType extends Model
{
    protected $table = 'studenttype';
    protected $primaryKey = 'studenttype_id';
    public $timestamps = false;
    
    protected $fillable = ['studenttypename'];
    
    public function students()
    {
        return $this->hasMany(Student::class, 'studenttype_id');
    }
}
