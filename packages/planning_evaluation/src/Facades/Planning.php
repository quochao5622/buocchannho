<?php

namespace Quochao56\PlanningEvaluation\Facades;

use Quochao56\Planning\Planning as Quochao56PlanningPlanning;
use Quochao56\PlanningEvaluation\Planning as Quochao56PlanningEvaluationPlanning;
use Illuminate\Support\Facades\Facade;

/**
 * @see Quochao56PlanningPlanning
 */
class Planning extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Quochao56PlanningEvaluationPlanning::class;
    }
}
