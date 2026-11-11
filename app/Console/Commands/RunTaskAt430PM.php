<?php

namespace App\Console\Commands;

use App\Console\Thai2DReleaseCommand;

class RunTaskAt430PM extends Thai2DReleaseCommand
{
    protected $signature = 'command:runat430pm {--scheduled : Restrict execution to the automatic retry window}';

    protected $description = 'Release the completed official afternoon Thai 2D result once';

    protected int $clockId = 4;

    protected string $openTime = '16:30:00';
}
