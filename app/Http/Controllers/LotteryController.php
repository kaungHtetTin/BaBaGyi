<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class LotteryController extends Controller
{
    public function records(Request $req)
    {
        $filters = $req->validate([
            'type' => 'sometimes|integer|in:2,3',
            'year' => 'sometimes|integer|between:1900,9999',
            'month' => 'sometimes|integer|between:1,12',
        ]);
        $today = CarbonImmutable::now('Asia/Yangon')->startOfDay();
        $type = (int) ($filters['type'] ?? 2);
        $month = CarbonImmutable::create((int) ($filters['year'] ?? $today->year),
            (int) ($filters['month'] ?? $today->month), 1, 0, 0, 0, 'Asia/Yangon');

        $records = Lottery::with('clock:id,hour,minute,second,morning')
            ->where('lottery_type_id', $type)
            ->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)
            ->orderBy('created_at')->orderBy('id')->get();

        $resultsByDate = $records->map(function ($record) {
            $clock = $record->clock;
            $hour = $clock ? $clock->hour % 12 + ($clock->morning ? 0 : 12) : null;
            $time = $clock ? sprintf('%02d:%02d', $hour, $clock->minute) : 'unknown-'.$record->id;

            return ['date' => $record->created_at->copy()->timezone('Asia/Yangon')->toDateString(),
                'time' => $time, 'number' => (string) $record->number,
                'label' => $clock ? CarbonImmutable::createFromFormat('!H:i', $time)->format('h:i A') : 'Draw'];
        })->groupBy('date')->map(fn ($rows) => $rows->keyBy('time'));

        $draws = ['09:30' => '09:30 AM', '12:01' => '12:01 PM', '14:00' => '02:00 PM', '16:30' => '04:30 PM'];
        $days = collect();
        $gridEnd = $month->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY);
        for ($date = $month->startOfWeek(CarbonImmutable::MONDAY); $date->lessThanOrEqualTo($gridEnd); $date = $date->addDay()) {
            $inMonth = $date->month === $month->month && $date->year === $month->year;
            $saved = $resultsByDate->get($date->toDateString(), collect());
            $results = $saved->all();
            if ($inMonth && $type === 2 && ! $date->isWeekend()) {
                foreach ($draws as $time => $label) {
                    if (! isset($results[$time])) {
                        $results[$time] = ['time' => $time, 'label' => $label, 'number' => null];
                    }
                }
            }
            ksort($results);
            $days->push(['date' => $date, 'in_month' => $inMonth, 'is_today' => $date->isSameDay($today),
                'is_weekend' => $date->isWeekend(), 'results' => $results]);
        }

        return view('admin.calendar-2d', [
            'page_name' => 'Calendar', 'type' => $type, 'selectedMonth' => $month, 'today' => $today,
            'previousMonth' => $month->subMonth(), 'nextMonth' => $month->addMonth(),
            'calendarWeeks' => $days->chunk(7), 'recordCount' => $records->count(),
            'resultDayCount' => $resultsByDate->count(),
        ]);
    }
}
