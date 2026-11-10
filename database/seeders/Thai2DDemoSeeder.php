<?php

namespace Database\Seeders;

use App\Models\Clock;
use App\Models\LotteryType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Thai2DDemoSeeder extends Seeder
{
    // private const DRAWS = [
    //     '09:30' => ['hour' => 9, 'minute' => 30, 'morning' => 1],
    //     '12:01' => ['hour' => 12, 'minute' => 1, 'morning' => 0],
    //     '14:00' => ['hour' => 2, 'minute' => 0, 'morning' => 0],
    //     '16:30' => ['hour' => 4, 'minute' => 30, 'morning' => 0],
    // ];

    // public function run(): void
    // {
    //     $now = CarbonImmutable::now('Asia/Yangon');
    //     $start = $now->startOfYear()->month(10);
    //     $end = $now->min($start->addMonth()->endOfMonth());
    //     if ($now->lessThan($start)) {
    //         $this->command?->info('No October/November demo dates have elapsed this year.');

    //         return;
    //     }

    //     [$inserted, $skipped] = DB::transaction(function () use ($now, $start, $end) {
    //         // Use the same lock as automatic history capture; never replace saved results.
    //         if (! LotteryType::whereKey(2)->lockForUpdate()->first()) {
    //             throw new RuntimeException('Thai 2D lottery type (ID 2) must be configured before seeding demo history.');
    //         }

    //         $clocks = [];
    //         foreach (self::DRAWS as $time => $attributes) {
    //             $clock = Clock::where($attributes)->orderBy('id')->first();
    //             if (! $clock) {
    //                 throw new RuntimeException("Configure the {$time} clock before seeding Thai 2D demo history.");
    //             }
    //             $clocks[$time] = $clock->id;
    //         }

    //         $inserted = 0;
    //         $skipped = 0;
    //         for ($day = $start; $day->startOfDay()->lessThanOrEqualTo($end); $day = $day->addDay()) {
    //             if ($day->isWeekend()) {
    //                 continue;
    //             }
    //             foreach ($clocks as $time => $clockId) {
    //                 $drawAt = $day->setTimeFromTimeString($time.':00');
    //                 if ($drawAt->greaterThan($now)) {
    //                     continue;
    //                 }
    //                 if (DB::table('lotteries')->where('lottery_type_id', 2)->where('clock_id', $clockId)
    //                     ->whereDate('created_at', $day->toDateString())->exists()) {
    //                     $skipped++;

    //                     continue;
    //                 }

    //                 // Stable, invented demo values; reruns do not change the displayed digits.
    //                 $value = hexdec(substr(hash('sha256', 'thai-2d-demo:'.$drawAt->format('Y-m-d H:i')), 0, 6)) % 100;
    //                 DB::table('lotteries')->insert([
    //                     'lottery_type_id' => 2,
    //                     'clock_id' => $clockId,
    //                     'number' => sprintf('%02d', $value),
    //                     'created_at' => $drawAt->format('Y-m-d H:i:s'),
    //                     'updated_at' => $drawAt->format('Y-m-d H:i:s'),
    //                 ]);
    //                 $inserted++;
    //             }
    //         }

    //         return [$inserted, $skipped];
    //     });

    //     $this->command?->info("Thai 2D demo history: inserted {$inserted}, preserved {$skipped} existing records.");
    // }
}
