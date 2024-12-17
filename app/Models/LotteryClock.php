<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryClock extends Model
{
    use HasFactory;
    protected $table="lottery_clock";

    public function lottery_type(){
        return $this->belongsTo(LotteryType::class);
    }

    public function clock(){
        return $this->belongsTo(Clock::class);
    }
}
