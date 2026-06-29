<?php

namespace Quochao56\Employee;

class Employee
{
    public function generateCode(): string
    {
        $latestEmployee = Models\Employee::query()
            ->where('employee_code', 'LIKE', 'GV%')
            ->latest('id')
            ->first();
        $latestCode = $latestEmployee ? (int) str_replace('GV', '', $latestEmployee->employee_code) : 0;

        return 'GV'.str_pad($latestCode + 1, 3, '0', STR_PAD_LEFT);
    }
}
