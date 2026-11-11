<?php

namespace App\Services;

use App\Models\Clock;
use App\Models\Lottery;
use Carbon\CarbonImmutable;

class LotteryHistory
{
    public function draws(int $type): array
    {
        $times = $type === 3 ? ['15:30' => '03:30 PM'] : [
            '09:30' => '09:30 AM', '12:01' => '12:01 PM',
            '14:00' => '02:00 PM', '16:30' => '04:30 PM',
        ];
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
