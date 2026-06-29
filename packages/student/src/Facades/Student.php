<?php

namespace Quochao56\Student\Facades;

use Quochao56\Student\Student as Quochao56StudentStudent;
use Illuminate\Support\Facades\Facade;

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
