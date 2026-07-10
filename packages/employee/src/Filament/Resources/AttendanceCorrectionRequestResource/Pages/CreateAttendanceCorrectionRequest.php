<?php

namespace Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Quochao56\Core\Models\User;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource;

class CreateAttendanceCorrectionRequest extends CreateRecord
{
    protected static string $resource = AttendanceCorrectionRequestResource::class;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $admins = User::permission('attendance_correction_requests.approve')->get();
        $superAdmins = User::where('is_super_admin', true)->get();
        $approvers = $admins->merge($superAdmins)
            ->unique('id')
            ->reject(fn ($user) => $user->id === auth()->id());

        $employeeName = $record->employee?->name ?? auth()->user()?->name ?? 'Nhân viên';
        $dateStr = $record->attendance_date ? Carbon::parse($record->attendance_date)->format('d/m/Y') : '';
        $viewUrl = AttendanceCorrectionRequestResource::getUrl('edit', ['record' => $record->id]);

        foreach ($approvers as $approver) {
            Notification::make()
                ->warning()
                ->title('Yêu cầu điều chỉnh chấm công mới')
                ->body("{$employeeName} vừa tạo yêu cầu điều chỉnh chấm công ngày {$dateStr}. Vui lòng xem xét và duyệt.")
                ->icon('heroicon-o-clock')
                ->actions([
                    Action::make('view')
                        ->label('Xem & Duyệt')
                        ->url($viewUrl)
                        ->button()
                        ->color('warning')
                        ->markAsRead(),
                ])
                ->sendToDatabase($approver);
        }
    }
}
