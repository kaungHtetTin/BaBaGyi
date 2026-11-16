<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramAlert extends Model
{
    protected $guarded = [];
    protected $casts = ['next_attempt_at' => 'datetime', 'sent_at' => 'datetime'];
}
