<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Number extends Model
{
    use HasFactory;
    public function lottery_type(){
        return $this->belongsTo(LotteryType::class);
    }

    public function clock(){
        return $this->belongsTo(Clock::class);
    }

}
