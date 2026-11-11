<?php

namespace App\Services;

use App\Models\Clock;
use App\Models\Lottery;
use Carbon\CarbonImmutable;

class LotteryHistory
{
    public static function extraDrawsEnabled(): bool
    {
        return (bool) config('lottery.thai_2d_extra_draws_enabled', false);
    }

    public static function times(int $type = 2): array
    {
        if ($type === 3) {
            return ['15:30' => '03:30 PM'];
        }

        return self::extraDrawsEnabled()
            ? ['09:30' => '09:30 AM', '12:01' => '12:01 PM', '14:00' => '02:00 PM', '16:30' => '04:30 PM']
            : ['12:01' => '12:01 PM', '16:30' => '04:30 PM'];
    }

    public static function hiddenClockIds(): array
    {
        if (self::extraDrawsEnabled()) {
            return [];
        }

        return Clock::where(function ($clock) {
            $clock->where('hour', 9)->where('minute', 30)->where('morning', 1);
        })->orWhere(function ($clock) {
            $clock->whereIn('hour', [2, 14])->where('minute', 0)->where('morning', 0);
        })->pluck('id')->all();
    }

    public function draws(int $type): array
    {
        $times = self::times($type);
        $clocks = Clock::all()->keyBy(fn ($clock) => sprintf('%02d:%02d',
            $clock->hour % 12 + ($clock->morning ? 0 : 12), $clock->minute));

        return collect($times)->map(fn ($label, $time) => [
            'label' => $label, 'clock_id' => $clocks->get($time)?->id,
        ])->all();
    }

    public function day(int $type, CarbonImmutable $date): array
    {
        $draws = $this->draws($type);
        $records = Lottery::where('lottery_type_id', $type)
            ->whereDate('created_at', $date->toDateString())
            ->orderBy('created_at')->orderBy('id')->get()->keyBy('clock_id');
        foreach ($draws as $time => &$draw) {
            $draw['number'] = $records->get($draw['clock_id'])?->number;
            $draw['due'] = $date->setTimeFromTimeString($time.':00')
                ->lte(CarbonImmutable::now('Asia/Yangon'));
        }
        unset($draw);

        return $draws;
    }
}
