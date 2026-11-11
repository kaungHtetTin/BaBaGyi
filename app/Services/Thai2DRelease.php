<?php

namespace App\Services;

use App\Models\HotNumber;
use App\Models\Lottery;
use App\Models\LotteryType;
use App\Models\Number;
use App\Models\Voucher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class Thai2DRelease
{
    public function run(int $clockId, string $openTime): void
    {
        $date = CarbonImmutable::now('Asia/Yangon')->toDateString();
        if (! LotteryType::whereKey(2)->where('release_mode', 1)->exists()
            || $this->recorded($clockId, $date)) {
            return;
        }

        // Keep the network request outside the database transaction.
        $result = app(Thai2DOfficialResult::class)->fetch($openTime);
        if ($result['stock_date'] !== $date) {
            throw new RuntimeException('Thai 2D date changed during the API request; release was skipped.');
        }

        $released = DB::transaction(function () use ($clockId, $openTime, $date, $result) {
            // Shared with history corrections and snapshots. Recheck after obtaining
            // the lock so overlapping scheduler/manual runs cannot settle twice.
            $type = LotteryType::whereKey(2)->lockForUpdate()->firstOrFail();
            if ($type->release_mode != 1 || $this->recorded($clockId, $date)) {
                return false;
            }

            $lottery = new Lottery();
            $lottery->lottery_type_id = 2;
            $lottery->clock_id = $clockId;
            $lottery->number = $result['twod'];
            $lottery->created_at = CarbonImmutable::parse($date.' '.$openTime, 'Asia/Yangon');
            $lottery->save();

            $vouchers = Voucher::whereDate('created_at', $date)
                ->where('lottery_type_id', 2)->where('clock_id', $clockId);
            (clone $vouchers)->where('number', $result['twod'])->update(['win' => 1]);
            (clone $vouchers)->where('number', '!=', $result['twod'])
                ->update(['win' => 0, 'verified' => 1]);

            Number::where('clock_id', $clockId)->where('lottery_type_id', 2)
                ->update(['demand' => 0, 'disable' => 0]);
            HotNumber::where('clock_id', $clockId)->where('lottery_type_id', 2)->delete();

            return true;
        });

        if ($released) {
            Log::channel('single')->info('Lottery winning digit recorded.', [
                'lottery_type_id' => 2, 'clock_id' => $clockId,
                'draw_time' => substr($openTime, 0, 5), 'win_digit' => $result['twod'],
                'source' => 'result', 'open_time' => $result['open_time'],
                'stock_date' => $result['stock_date'], 'history_id' => $result['history_id'],
                'recorded_at' => now('Asia/Yangon')->toDateTimeString(), 'timezone' => 'Asia/Yangon',
            ]);
        }
    }

    private function recorded(int $clockId, string $date): bool
    {
        return Lottery::where('lottery_type_id', 2)->where('clock_id', $clockId)
            ->whereDate('created_at', $date)->exists();
    }
}
