<?php

namespace Quochao56\Scheduler\Filament\Resources\ClassroomResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Quochao56\Core\Enum\BaseStatusEnum;

class ClassroomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(trans('packages.scheduler::scheduler.classrooms.fields.name'))
                ->required()
                ->maxLength(255),

            Select::make('status')
                ->label(trans('packages.scheduler::scheduler.classrooms.fields.status'))
                ->options(BaseStatusEnum::class)
                ->default(BaseStatusEnum::Active)
                ->required(),

            Textarea::make('description')
                ->label(trans('packages.scheduler::scheduler.classrooms.fields.description'))
                ->rows(3)
                ->columnSpanFull()
                ->nullable(),
        ]);
    }
}
