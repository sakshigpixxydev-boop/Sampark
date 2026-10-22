<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    protected $primaryKey = 'permission_id';
    public $timestamps = false;
    
    protected $fillable = [
        'permkey',
        'permname',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'date',
    ];
    
    // Route model binding key
    public function getRouteKeyName()
    {
        return 'permission_id';
    }
    
    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rolepermission', 'permission_id', 'role_id');
    }
}
