<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    protected $table = 'ms_relation';
    protected $primaryKey = 'relation_id';
    public $timestamps = false;
    
    protected $fillable = ['relation'];
    
    public function guardians()
    {
        return $this->hasMany(Guardian::class, 'relation_id');
    }
}
