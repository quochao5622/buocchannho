<?php

namespace Quochao56\Employee\Commands;

use Illuminate\Console\Command;

class EmployeeCommand extends Command
{
    public $signature = 'employee';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
