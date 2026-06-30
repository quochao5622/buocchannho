<?php

namespace Quochao56\Student\Filament\Resources\StudentResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Quochao56\Core\Enum\BaseStatusEnum;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('student_code')
                ->label(trans('packages.student::student.fields.student_code'))
                ->maxLength(50)
                ->unique(table: 'students', column: 'student_code', ignoreRecord: true),
            TextInput::make('name')
                ->label(trans('packages.student::student.fields.name'))
                ->required()
                ->maxLength(255),

            TextInput::make('nickname')
                ->label(trans('packages.student::student.fields.nickname'))
                ->maxLength(255),
            Select::make('gender')
                ->label(trans('packages.student::student.fields.gender'))
                ->options([
                    'male' => trans('packages.student::student.gender.male'),
                    'female' => trans('packages.student::student.gender.female'),
                    'other' => trans('packages.student::student.gender.other'),
                ]),
            DatePicker::make('dob')
                ->label(trans('packages.student::student.fields.dob'))
                ->displayFormat('d/m/Y')
                ->native(false),
            TextInput::make('father_name')
                ->label(trans('packages.student::student.fields.father_name'))
                ->maxLength(255),

            TextInput::make('father_phone')
                ->label(trans('packages.student::student.fields.father_phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('mother_name')
                ->label(trans('packages.student::student.fields.mother_name'))
                ->maxLength(255),

            TextInput::make('mother_phone')
                ->label(trans('packages.student::student.fields.mother_phone'))
                ->tel()
                ->maxLength(20),

            Select::make('status')
                ->label(trans('packages.student::student.fields.status'))
                ->options([
                    BaseStatusEnum::Active->value => BaseStatusEnum::Active->getLabel(),
                    BaseStatusEnum::Inactive->value => BaseStatusEnum::Inactive->getLabel(),
                ])
                ->default(BaseStatusEnum::Active->value)
                ->required(),
            FileUpload::make('avatar')
                ->label(trans('packages.student::student.fields.avatar'))
                ->image()
                ->directory('students')
                ->imageEditor(),

        ]);
    }
}
