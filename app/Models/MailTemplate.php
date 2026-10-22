<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    protected $table = 'mail_templates';
    protected $primaryKey = 'mail_template_id';

    protected $fillable = [
        'identifier',
        'title_en',
        'title_hi',
        'title_gu',
        'content_en',
        'content_hi',
        'content_gu',
    ];
}
