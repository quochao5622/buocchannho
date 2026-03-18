<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Employee\Employee;
use Quochao56\Employee\Filament\Resources\EmployeeResource;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        if (empty($data['employee_code'])) {
            $data['employee_code'] = (new Employee())->generateCode();
        }
        return static::getModel()::create($data);
    }
}
