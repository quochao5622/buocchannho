<?php

namespace Quochao56\Student\Filament\Resources\StudentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Student\Filament\Resources\StudentResource;
use Quochao56\Student\Student;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    
    protected function handleRecordCreation(array $data): Model
    {
        if (empty($data['student_code'])) {
            $data['student_code'] = (new Student())->generateStudentCode();
        }
        return static::getModel()::create($data);
    }
}
