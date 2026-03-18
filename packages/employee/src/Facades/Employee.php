<?php

namespace Quochao56\Employee\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Quochao56\Employee\Employee
 */
class Employee extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quochao56\Employee\Employee::class;
    }
}
