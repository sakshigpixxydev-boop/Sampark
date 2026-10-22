<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianMessageQueue extends Model
{
    protected $table = 'guardian_message_queue';

    protected $fillable = [
        'user_id',
        'guardian_message_id',
        'status',
    ];

    public function message()
    {
        return $this->belongsTo(GuardianMessage::class, 'guardian_message_id');
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class, 'user_id', 'guardian_id');
    }
}
