<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lottery extends Model
{
    use HasFactory;

    public function scopeVisibleDraws($query)
    {
        if (! \App\Services\LotteryHistory::extraDrawsEnabled()) {
            $query->where(function ($query) {
                $query->where('lottery_type_id', '!=', 2)
                    ->orWhereNotIn('clock_id', \App\Services\LotteryHistory::hiddenClockIds());
            });
        }

        return $query;
    }

    public function clock(){
        return $this->belongsTo(Clock::class);
    }
}
