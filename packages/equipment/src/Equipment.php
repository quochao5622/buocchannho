<?php

namespace Quochao56\Equipment;

class Equipment
{
    public function generateEquipmentCode(): string
    {
        $latestEquipment = Models\Equipment::latest()->first();
        $latestCode = $latestEquipment ? (int) str_replace('TB', '', $latestEquipment->equipment_code) : 0;
        return 'TB' . str_pad($latestCode + 1, 3, '0', STR_PAD_LEFT);
    }
}

