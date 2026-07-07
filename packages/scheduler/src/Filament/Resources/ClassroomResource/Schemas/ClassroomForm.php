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
                ->label('Tên phòng học')
                ->required()
                ->maxLength(255),

            Select::make('status')
                ->label('Trạng thái')
                ->options(BaseStatusEnum::class)
                ->default(BaseStatusEnum::Active)
                ->required(),

            Textarea::make('description')
                ->label('Mô tả/Ghi chú')
                ->rows(3)
                ->columnSpanFull()
                ->nullable(),
        ]);
    }
}
