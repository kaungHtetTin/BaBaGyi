<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function lottery_type(){
        return $this->belongsTo(LotteryType::class);
    }

    public function clock(){
        return $this->belongsTo(Clock::class);
    }

    public function verified_by($admin_id){
        return User::find($admin_id);
    }
}
