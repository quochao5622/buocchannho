<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource;

class EditEmployeeAttendance extends EditRecord
{
    protected static string $resource = EmployeeAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->record;
        $actions = [];

        if ($record->flagged_location && $record->verification_status === 'pending' && (auth()->user()?->can('approve_flagged_location', 'employee_attendances') || auth()->user()?->isSuperAdmin())) {
            $actions[] = Action::make('approve')
                ->label(trans('packages.employee::employee_attendance.actions.approve'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () use ($record) {
                    $record->update([
                        'verification_status' => 'approved',
                    ]);
                    Notification::make()
                        ->title('Đã duyệt chấm công')
                        ->body("Chấm công của {$record->employee->name} ngày {$record->date->format('d/m/Y')} đã được duyệt.")
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                });

            $actions[] = Action::make('reject')
                ->label(trans('packages.employee::employee_attendance.actions.reject'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Textarea::make('reject_reason')
                        ->label('Lý do từ chối')
                        ->placeholder('Nhập lý do từ chối chấm công này...')
                        ->rows(3)
                        ->required(),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'verification_status' => 'rejected',
                        'notes' => ($record->notes ? $record->notes."\n" : '').'Từ chối: '.($data['reject_reason'] ?? 'Không có lý do'),
                    ]);
                    Notification::make()
                        ->title('Đã từ chối chấm công')
                        ->body("Chấm công của {$record->employee->name} ngày {$record->date->format('d/m/Y')} đã bị từ chối.")
                        ->danger()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                });
        }

        return $actions;
    }
}
