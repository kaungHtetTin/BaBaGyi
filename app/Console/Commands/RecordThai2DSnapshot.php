<?php

namespace App\Console\Commands;

use App\Models\Clock;
use App\Models\Lottery;
use App\Models\LotteryType;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecordThai2DSnapshot extends Command
{
    protected $signature = 'lottery:record-thai-2d {time : Myanmar time: 09:30 or 14:00}';

    protected $description = 'Save a Thai 2D history snapshot without processing vouchers or payouts';

    private const TIMES = [
        '09:30' => ['hour' => 9, 'minute' => 30, 'morning' => 1],
        '14:00' => ['hour' => 2, 'minute' => 0, 'morning' => 0],
    ];

    public function handle()
    {
        $time = $this->argument('time');
        if (! isset(self::TIMES[$time])) {
            $this->error('Supported snapshot times are 09:30 and 14:00.');

            return Command::FAILURE;
        }

        $now = CarbonImmutable::now('Asia/Yangon');
        $target = $now->setTimeFromTimeString($time.':00');
        if ($now->lt($target->subMinute()) || $now->gte($target->addMinute())) {
            return $this->failedSnapshot($time, 'The command must start during the minute before or the scheduled Myanmar minute.');
        }

        if ($now->isWeekend()) {
            $this->info('No Thai 2D snapshot is recorded on weekends.');

            return Command::SUCCESS;
        }

        try {
            // These clocks already exist; do not enable extra betting draws.
            $clock = Clock::where(self::TIMES[$time])->first();
            if (! $clock) {
                return $this->failedSnapshot($time, 'The matching clock is missing.');
            }

            $records = fn () => Lottery::where('lottery_type_id', 2)
                ->where('clock_id', $clock->id)
                ->whereDate('created_at', $now->toDateString());

            if ($records()->exists()) {
                $this->info('This Thai 2D snapshot has already been recorded.');

                return Command::SUCCESS;
            }

            // Start before the draw so scheduler startup latency does not choose the digit.
            // Bound polling with a monotonic clock even if the API clock stops advancing.
            $deadline = hrtime(true) + 90_000_000_000;
            do {
                $payload = Http::acceptJson()->withHeaders(['Cache-Control' => 'no-cache'])
                    ->connectTimeout(3)->timeout(5)
                    ->get('https://api.thaistock2d.com/live')
                    ->throw()->json();
                $serverValue = is_array($payload) ? ($payload['server_time'] ?? null) : null;
                if (! is_string($serverValue)) {
                    return $this->failedSnapshot($time, 'The API server_time is missing or invalid.');
                }
                $serverTime = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $serverValue, 'Asia/Yangon');
                if ($serverTime->format('Y-m-d H:i:s') !== $serverValue
                    || $serverTime->toDateString() !== $target->toDateString()) {
                    return $this->failedSnapshot($time, 'The API server_time has an invalid format or date.');
                }
                if ($serverTime->gt($target)) {
                    return $this->failedSnapshot($time, 'Exact API server second was missed; no later digit was saved.', [
                        'target_time' => $target->toDateTimeString(),
                        'server_time' => $serverValue,
                    ]);
                }
                if ($serverTime->equalTo($target)) {
                    break;
                }
                if (hrtime(true) >= $deadline) {
                    return $this->failedSnapshot($time, 'Timed out waiting for the exact API server second.');
                }
                // Poll five times per second near the target; once per second earlier.
                usleep($target->getTimestamp() - $serverTime->getTimestamp() <= 2 ? 200_000 : 1_000_000);
            } while (true);

            $live = $payload['live'] ?? null;

            if (! is_array($live)
                || ! is_string($live['twod'] ?? null)
                || ! preg_match('/\A[0-9]{2}\z/', $live['twod'])
                || ($live['date'] ?? null) !== $now->toDateString()
                || ! is_string($live['time'] ?? null)) {
                return $this->failedSnapshot($time, 'The API did not supply a valid two-digit number for today.');
            }

            $sourceTime = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', $live['time'], 'Asia/Yangon');
            $age = $serverTime->getTimestamp() - $sourceTime->getTimestamp();
            if ($sourceTime->format('Y-m-d H:i:s') !== $live['time']
                || $sourceTime->toDateString() !== $now->toDateString()
                || $age < 0 || $age > 300) {
                return $this->failedSnapshot($time, 'The API live timestamp is stale or invalid.');
            }

            $saved = DB::transaction(function () use ($records, $clock, $live, $target) {
                // Serialize concurrent captures for this lottery type before checking again.
                // History recording is independent of open/release_mode settings.
                LotteryType::whereKey(2)->lockForUpdate()->firstOrFail();
                if ($records()->exists()) {
                    return false;
                }

                $lottery = new Lottery();
                $lottery->lottery_type_id = 2;
                $lottery->clock_id = $clock->id;
                $lottery->number = $live['twod'];
                $lottery->created_at = $target;
                $lottery->updated_at = $target;
                $lottery->save();

                return true;
            });

            if ($saved) {
                Log::channel('single')->info('Lottery winning digit recorded.', [
                    'lottery_type_id' => 2,
                    'clock_id' => $clock->id,
                    'draw_time' => $time,
                    'win_digit' => $live['twod'],
                    'server_time' => $serverValue,
                    'live_time' => $live['time'],
                    'recorded_at' => CarbonImmutable::now('Asia/Yangon')->toDateTimeString(),
                    'timezone' => 'Asia/Yangon',
                ]);
            }

            $this->info($saved ? "Thai 2D {$time} snapshot saved." : 'This Thai 2D snapshot has already been recorded.');

            return Command::SUCCESS;
        } catch (Throwable $exception) {
            return $this->failedSnapshot($time, $exception->getMessage());
        }
    }

    private function failedSnapshot(string $time, string $reason, array $context = []): int
    {
        Log::warning('Thai 2D history snapshot was not saved.', array_merge(['time' => $time, 'reason' => $reason], $context));
        $this->warn('Thai 2D snapshot was not saved: '.$reason);

        return Command::FAILURE;
    }
}
