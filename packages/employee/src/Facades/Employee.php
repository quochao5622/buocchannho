<?php

namespace Quochao56\Employee\Facades;

use Quochao56\Employee\Employee as Quochao56EmployeeEmployee;
use Illuminate\Support\Facades\Facade;

/**
 * @see Quochao56EmployeeEmployee
 */
class Employee extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Quochao56EmployeeEmployee::class;
    }
}
