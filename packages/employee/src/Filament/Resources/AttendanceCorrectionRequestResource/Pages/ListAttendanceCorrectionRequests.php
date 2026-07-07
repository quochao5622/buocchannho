<?php

namespace Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource;

class ListAttendanceCorrectionRequests extends ListRecords
{
    protected static string $resource = AttendanceCorrectionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
