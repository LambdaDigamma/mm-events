<?php

namespace LambdaDigamma\MMEvents\Commands;

use Illuminate\Console\Command;

class MMEventsCommand extends Command
{
    public $signature = 'mm-events';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
