<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianMessage extends Model
{
    protected $table = 'guardian_message';

    protected $fillable = [
        'subject',
        'message',
        'status',
    ];

    public function queue()
    {
        return $this->hasMany(GuardianMessageQueue::class, 'guardian_message_id');
    }
}
