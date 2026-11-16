<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSetting extends Model
{
    protected $guarded = [];
    protected $hidden = ['bot_token'];
    protected $casts = [
        'bot_token' => 'encrypted',
        'enabled' => 'boolean',
        'notify_topups' => 'boolean',
        'notify_withdrawals' => 'boolean',
    ];

    public static function current(): self
    {
        return static::find(1) ?? new static(['id' => 1, 'notify_topups' => true, 'notify_withdrawals' => true]);
    }
}
