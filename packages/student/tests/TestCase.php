<?php

namespace Quochao56\Student\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Quochao56\Core\CoreServiceProvider;
use Quochao56\Employee\EmployeeServiceProvider;
use Quochao56\PlanningEvaluation\PlanningEvaluationServiceProvider;
use Quochao56\Student\StudentServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
            EmployeeServiceProvider::class,
            PlanningEvaluationServiceProvider::class,
            StudentServiceProvider::class,
        ];
    }
}
