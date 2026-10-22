<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRead extends Model
{
    protected $table = 'user_reads';

    protected $fillable = [
        'sysuser_id',
        'readable_id',
        'readable_type',
    ];

    public function user()
    {
        return $this->belongsTo(SysUser::class, 'sysuser_id');
    }
}
