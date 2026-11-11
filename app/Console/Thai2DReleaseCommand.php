<?php

namespace App\Console;

use App\Services\Thai2DRelease;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Thai2DReleaseCommand extends Command
{
    protected int $clockId;

    protected string $openTime;

    public function handle(): int
    {
        $now = CarbonImmutable::now('Asia/Yangon');
        $start = $now->setTimeFromTimeString($this->openTime);
        $deadline = $start->addMinutes(15);
        if ($this->option('scheduled') && ($now->lt($start) || $now->gte($deadline->addMinute()))) {
            return self::SUCCESS;
        }

        try {
            app(Thai2DRelease::class)->run($this->clockId, $this->openTime);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $pending = $this->option('scheduled') && $now->lt($deadline);
            Log::log($pending ? 'info' : 'warning', $pending
                ? 'Thai 2D release pending; scheduler will retry next minute.'
                : 'Thai 2D release failed; admin review required.', [
                    'clock_id' => $this->clockId, 'open_time' => $this->openTime,
                    'date' => $start->toDateString(), 'reason' => $exception->getMessage(),
                    'retry_until' => $deadline->toDateTimeString(),
                ]);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
