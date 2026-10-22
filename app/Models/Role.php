<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;
    
    protected $fillable = [
        'rolename',
        'ispredefined',
    ];
    
    protected $casts = [
        'ispredefined' => 'boolean',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];
    
    // Relationships
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'rolepermission', 'role_id', 'permission_id');
    }
    
    public function users()
    {
        return $this->hasMany(SysUser::class, 'role_id');
    }
}
