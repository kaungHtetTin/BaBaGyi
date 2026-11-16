<?php

namespace App\Console\Commands;

use App\Services\TelegramNotifier;
use Illuminate\Console\Command;

class SendTelegramAlerts extends Command
{
    protected $signature = 'telegram:send-pending';
    protected $description = 'Deliver pending Telegram financial alerts and retry failed deliveries';

    public function handle(TelegramNotifier $notifier)
    {
        $notifier->flush();
        return self::SUCCESS;
    }
}
