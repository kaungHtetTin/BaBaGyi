<?php

namespace App\Console\Commands;

use App\Console\Thai2DReleaseCommand;

class RunTaskAt1201PM extends Thai2DReleaseCommand
{
    protected $signature = 'command:runat1201pm {--scheduled : Restrict execution to the automatic retry window}';

    protected $description = 'Release the completed official noon Thai 2D result once';

    protected int $clockId = 2;

    protected string $openTime = '12:01:00';
}
