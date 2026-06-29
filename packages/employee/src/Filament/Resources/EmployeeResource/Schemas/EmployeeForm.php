<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeResource\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Enum\BaseStatusEnum;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('employee_code')
                ->label(trans('packages.employee::employee.fields.employee_code'))
                ->maxLength(50)
                ->unique(table: 'employees', column: 'employee_code', ignoreRecord: true),

            TextInput::make('name')
                ->label(trans('packages.employee::employee.fields.name'))
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label(trans('packages.employee::employee.fields.email'))
                ->email()
                ->unique(table: 'employees', column: 'email', ignoreRecord: true)
                ->maxLength(255),

            Select::make('gender')
                ->label(trans('packages.employee::employee.fields.gender'))
                ->options([
                    'male' => trans('packages.employee::employee.gender.male'),
                    'female' => trans('packages.employee::employee.gender.female'),
                    'other' => trans('packages.employee::employee.gender.other'),
                ]),

            DatePicker::make('dob')
                ->label(trans('packages.employee::employee.fields.dob'))
                ->displayFormat('d/m/Y')
                ->native(false),

            TextInput::make('phone')
                ->label(trans('packages.employee::employee.fields.phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('address')
                ->label(trans('packages.employee::employee.fields.address'))
                ->maxLength(255),

            TextInput::make('position')
                ->label(trans('packages.employee::employee.fields.position'))
                ->maxLength(255),

            Select::make('employment_type')
                ->label(trans('packages.employee::employee.fields.employment_type'))
                ->options([
                    'full-time' => trans('packages.employee::employee.employment_type.full_time'),
                    'part-time' => trans('packages.employee::employee.employment_type.part_time'),
                    'intern' => trans('packages.employee::employee.employment_type.intern'),
                    'contract' => trans('packages.employee::employee.employment_type.contract'),
                ]),

            DatePicker::make('hired_at')
                ->label(trans('packages.employee::employee.fields.hired_at'))
                ->displayFormat('d/m/Y')
                ->native(false),

            DatePicker::make('probation_end_at')
                ->label(trans('packages.employee::employee.fields.probation_end_at'))
                ->displayFormat('d/m/Y')
                ->native(false),

            FileUpload::make('avatar')
                ->label(trans('packages.employee::employee.fields.avatar'))
                ->image()
                ->directory('employees')
                ->imageEditor(),

            Select::make('status')
                ->label(trans('packages.employee::employee.fields.status'))
                ->options([
                    BaseStatusEnum::Active->value => BaseStatusEnum::Active->getLabel(),
                    BaseStatusEnum::Inactive->value => BaseStatusEnum::Inactive->getLabel(),
                ])
                ->default(BaseStatusEnum::Active->value)
                ->required(),
        ]);
    }
}
