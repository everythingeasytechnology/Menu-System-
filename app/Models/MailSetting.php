<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'enabled',
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'timeout',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'port' => 'integer',
        'timeout' => 'integer',
        'password' => 'encrypted',
        'last_tested_at' => 'datetime',
    ];
}
