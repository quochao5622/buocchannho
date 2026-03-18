<?php

namespace Quochao56\Student\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Quochao56\Student\Student
 */
class Student extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quochao56\Student\Student::class;
    }
}
