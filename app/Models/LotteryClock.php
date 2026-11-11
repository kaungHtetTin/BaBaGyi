<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryClock extends Model
{
    use HasFactory;
    protected $table="lottery_clock";

    protected static function booted()
    {
        static::addGlobalScope('visible_draws', function ($query) {
            if (! \App\Services\LotteryHistory::extraDrawsEnabled()) {
                $query->where(function ($query) {
                    $query->where('lottery_clock.lottery_type_id', '!=', 2)
                        ->orWhereNotIn('lottery_clock.clock_id', \App\Services\LotteryHistory::hiddenClockIds());
                });
            }
        });
    }

    public function lottery_type(){
        return $this->belongsTo(LotteryType::class);
    }

    public function clock(){
        return $this->belongsTo(Clock::class);
    }
}
