<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $table = 'ticket_categories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'emails',
    ];

    protected $casts = [
        'emails' => 'array',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'ticket_category_id');
    }
}
