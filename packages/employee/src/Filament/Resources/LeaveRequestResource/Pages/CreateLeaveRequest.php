<?php

namespace Quochao56\Employee\Filament\Resources\LeaveRequestResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Quochao56\Core\Models\User;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $admins = User::permission('leave_requests.approve')->get();
        $superAdmins = User::where('is_super_admin', true)->get();
        $approvers = $admins->merge($superAdmins)
            ->unique('id')
            ->reject(fn ($user) => $user->id === auth()->id());

        $employeeName = $record->employee?->name ?? auth()->user()?->name ?? 'Nhân viên';
        $startDate = $record->start_date ? Carbon::parse($record->start_date)->format('d/m/Y') : '';
        $viewUrl = LeaveRequestResource::getUrl('edit', ['record' => $record->id]);

        foreach ($approvers as $approver) {
            Notification::make()
                ->warning()
                ->title('Yêu cầu nghỉ phép mới')
                ->body("{$employeeName} vừa xin nghỉ phép từ ngày {$startDate}. Vui lòng xem xét và duyệt.")
                ->icon('heroicon-o-calendar-days')
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
