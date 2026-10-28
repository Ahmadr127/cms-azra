<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogTera extends Model
{
    protected $table = 'log_tera';

    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'url',
        'status',
        'phone',
        'body_post',
        'response',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
