<?php

namespace Quochao56\Student\Facades;

use Illuminate\Support\Facades\Facade;
use Quochao56\Student\Student as Quochao56StudentStudent;

/**
 * @see Quochao56StudentStudent
 */
class Student extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Quochao56StudentStudent::class;
    }
}
