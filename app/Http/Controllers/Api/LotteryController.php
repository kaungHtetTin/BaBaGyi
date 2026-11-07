<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clock;
use App\Models\Lottery;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotteryController extends Controller
{
    private const THAI_2D_DRAWS = [
        '09:30' => '09:30 AM',
        '12:01' => '12:01 PM',
        '14:00' => '02:00 PM',
        '16:30' => '04:30 PM',
    ];

    public function records(Request $req)
    {
        [$year, $month, $type] = $this->monthParameters($req);

        return response()->json($this->monthlyRecords($year, $month, $type));
    }

    public function calendar(Request $req)
    {
        [$year, $month] = $this->monthParameters($req, true);
        $records = $this->monthlyRecords($year, $month, 2)->groupBy('date');
        $clocks = Clock::orderBy('id')->get(['id', 'hour', 'minute', 'morning']);
        $draws = [];
        foreach (self::THAI_2D_DRAWS as $time => $label) {
            $clock = $clocks->first(fn ($clock) => $this->clockTime($clock) === $time);
            $draws[] = ['clock_id' => $clock?->id, 'draw_time' => $time,
                'draw_label' => $label, 'record_only' => in_array($time, ['09:30', '14:00'], true)];
        }

        $days = [];
        $firstDay = CarbonImmutable::create($year, $month, 1, 0, 0, 0, 'Asia/Yangon');
        for ($day = $firstDay; $day->month === $month; $day = $day->addDay()) {
            if ($day->isWeekend()) {
                continue;
            }

            $results = array_fill_keys(array_keys(self::THAI_2D_DRAWS), null);
            foreach ($records->get($day->toDateString(), collect()) as $record) {
                if (array_key_exists($record->draw_time ?? '', $results)) {
                    // Preserve the most recent entry if older releases contain duplicates.
                    $results[$record->draw_time] = $record;
                }
            }
            $days[] = ['date' => $day->toDateString(), 'day' => $day->day,
                'd' => $day->format('D'), 'results' => $results];
        }

        return response()->json(['year' => $year, 'month' => $month, 'type' => 2,
            'timezone' => 'Asia/Yangon', 'draws' => $draws, 'days' => $days]);
    }

    private function monthParameters(Request $req, bool $calendar = false): array
    {
        $values = $req->validate([
            'year' => 'sometimes|integer|between:1900,9999',
            'month' => 'sometimes|integer|between:1,12',
            'type' => $calendar ? 'sometimes|integer|in:2' : 'sometimes|integer|min:1',
        ]);
        $now = CarbonImmutable::now('Asia/Yangon');

        return [(int) ($values['year'] ?? $now->year), (int) ($values['month'] ?? $now->month),
            (int) ($values['type'] ?? 2)];
    }

    private function monthlyRecords(int $year, int $month, int $type)
    {
        return Lottery::with('clock:id,hour,minute,second,morning')
            ->whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->where('lottery_type_id', $type)
            ->orderBy('created_at')->orderBy('id')->get()
            ->filter(fn ($record) => $type !== 2 || ! $record->created_at->isWeekend())
            ->each(function ($record) use ($type) {
                $date = $record->created_at->copy()->timezone('Asia/Yangon');
                $time = $record->clock ? $this->clockTime($record->clock) : null;
                $record->day = $date->day;
                $record->d = $date->format('D');
                $record->date = $date->toDateString();
                $record->draw_time = $time;
                $record->draw_label = $time === null ? null
                    : CarbonImmutable::createFromFormat('!H:i', $time, 'Asia/Yangon')->format('h:i A');
                $record->record_only = $type === 2 && in_array($time, ['09:30', '14:00'], true);
            })
            ->sortBy(fn ($record) => $record->date.' '.($record->draw_time ?? '99:99'))
            ->values();
    }

    private function clockTime(Clock $clock): string
    {
        $hour = $clock->hour % 12 + ($clock->morning ? 0 : 12);

        return sprintf('%02d:%02d', $hour, $clock->minute);
    }

    public function _3d_records(Request $req)
    {
        if (isset($req->year)) {
            $year = $req->year;
        } else {
            $year = date('Y');
        }

        $records = Lottery::selectRaw('*, Month(created_at) as month')
        ->where(DB::raw('YEAR(created_at)'), $year)
        ->where('lottery_type_id', 3)
        ->orderBy('created_at', 'asc')
        ->get();

        return $records;
    }
}
