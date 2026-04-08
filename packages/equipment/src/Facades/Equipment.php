<?php

namespace Quochao56\Equipment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Quochao56\Equipment\Equipment
 */
class Equipment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quochao56\Equipment\Equipment::class;
    }
}

