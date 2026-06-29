<?php

namespace Quochao56\Equipment\Facades;

use Illuminate\Support\Facades\Facade;
use Quochao56\Equipment\Equipment as Quochao56EquipmentEquipment;

/**
 * @see Quochao56EquipmentEquipment
 */
class Equipment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Quochao56EquipmentEquipment::class;
    }
}
