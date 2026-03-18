<?php

namespace Quochao56\Student\Commands;

use Illuminate\Console\Command;

class StudentCommand extends Command
{
    public $signature = 'student';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
