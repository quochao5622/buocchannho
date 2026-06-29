<?php

namespace Quochao56\PlanningEvaluation\Facades;

use Illuminate\Support\Facades\Facade;
use Quochao56\Planning\Planning as Quochao56PlanningPlanning;
use Quochao56\PlanningEvaluation\Planning as Quochao56PlanningEvaluationPlanning;

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
