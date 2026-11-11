<?php

namespace App\Http\Controllers;

use App\Models\Lottery;
use App\Models\LotteryType;
use App\Services\LotteryHistory;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LotteryController extends Controller
{
    public function editDay(Request $req, LotteryHistory $history)
    {
        $data = $this->dayFilters($req);
        $date = CarbonImmutable::parse($data['date'], 'Asia/Yangon');

        return view('admin.history-edit', [
            'page_name' => 'Edit history', 'date' => $date, 'type' => (int) $data['type'],
            'draws' => $history->day((int) $data['type'], $date),
            'source' => $data['source'] ?? 'calendar', 'returnYear' => $data['year'] ?? null,
            'returnUrl' => $this->historyReturnUrl($data),
        ]);
    }

    public function updateDay(Request $req, LotteryHistory $history)
    {
        $data = $this->dayFilters($req);
        $type = (int) $data['type'];
        $date = CarbonImmutable::parse($data['date'], 'Asia/Yangon');
        $draws = $history->day($type, $date);
        $rules = ['numbers' => 'required|array:'.implode(',', array_keys($draws))];
        foreach ($draws as $time => $draw) {
            $rules['numbers.'.$time] = ['nullable', 'string', 'regex:/\A[0-9]{'.$type.'}\z/'];
        }
        $numbers = $req->validate($rules)['numbers'];
        foreach ($numbers as $time => $number) {
            if ($number !== null && (! $draws[$time]['clock_id'] || ! $draws[$time]['due'])) {
                throw ValidationException::withMessages(['numbers.'.$time => 'This draw is not available for correction yet.']);
            }
        }

        $changes = DB::transaction(function () use ($type, $date, $draws, $numbers) {
            // Use the same lock as automatic snapshots to avoid duplicate missing records.
            LotteryType::whereKey($type)->lockForUpdate()->firstOrFail();
            $changes = [];
            foreach ($numbers as $time => $number) {
                if ($number === null) {
                    continue;
                }
                $record = Lottery::where('lottery_type_id', $type)
                    ->where('clock_id', $draws[$time]['clock_id'])
                    ->whereDate('created_at', $date->toDateString())
                    ->orderByDesc('created_at')->orderByDesc('id')->first();
                $before = $record?->number;
                if ($record && (string) $before === $number) {
                    continue;
                }
                if (! $record) {
                    $record = new Lottery();
                    $record->lottery_type_id = $type;
                    $record->clock_id = $draws[$time]['clock_id'];
                    $record->created_at = $date->setTimeFromTimeString($time.':00');
                }
                $record->number = $number;
                $record->save();
                $changes[] = ['record_id' => $record->id, 'draw_time' => $time,
                    'previous_number' => $before, 'number' => $number];
            }

            return $changes;
        });
        if ($changes) {
            Log::info('Lottery history corrected by admin.', ['admin_id' => $req->user()->id,
                'lottery_type_id' => $type, 'date' => $data['date'], 'changes' => $changes]);
        }

        return redirect($this->historyReturnUrl($data))->with('history_status',
            $changes ? 'Result history saved. Voucher win status and payouts were not changed.' : 'No history changes were made.');
    }

    private function dayFilters(Request $req): array
    {
        return $req->validate([
            'type' => 'required|integer|in:2,3',
            'date' => 'required|date_format:Y-m-d|after_or_equal:1900-01-01|before_or_equal:'.CarbonImmutable::now('Asia/Yangon')->toDateString(),
            'source' => 'sometimes|in:calendar,dashboard',
            'year' => 'nullable|integer|between:1900,9999',
        ]);
    }

    private function historyReturnUrl(array $data): string
    {
        $date = CarbonImmutable::parse($data['date'], 'Asia/Yangon');
        if (($data['source'] ?? 'calendar') === 'dashboard') {
            return route('admin.index', array_filter(['date' => $data['date'], 'year' => $data['year'] ?? null]));
        }

        return route('admin.lotteries.records', ['type' => $data['type'], 'year' => $date->year, 'month' => $date->month]);
    }

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
