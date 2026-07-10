<?php

namespace Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource;

class EditAttendanceCorrectionRequest extends EditRecord
{
    protected static string $resource = AttendanceCorrectionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
