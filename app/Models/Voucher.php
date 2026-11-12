<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    public function payoutAmount(): int
    {
        $multiplier = $this->lottery_type_id == 3 && $this->bonus_win == 1
            ? $this->lottery_type->bonus_coefficient
            : $this->lottery_type->coefficient;

        return (int) ($this->amount * $multiplier);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function lottery_type(){
        return $this->belongsTo(LotteryType::class);
    }

    public function clock(){
        return $this->belongsTo(Clock::class);
    }

    public function reviewer(){
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verified_by($admin_id){
        return User::find($admin_id);
    }
}
