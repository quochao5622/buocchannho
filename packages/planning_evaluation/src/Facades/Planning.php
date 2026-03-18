<?php

namespace Quochao56\PlanningEvaluation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Quochao56\Planning\Planning
 */
class Planning extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Quochao56\PlanningEvaluation\Planning::class;
    }
}
