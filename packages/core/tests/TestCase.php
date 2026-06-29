<?php

namespace Quochao56\Core\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Quochao56\Core\CoreServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
        ];
    }
}
