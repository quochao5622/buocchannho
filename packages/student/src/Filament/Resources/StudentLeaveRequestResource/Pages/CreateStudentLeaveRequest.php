<?php

namespace Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Quochao56\Core\Models\User;
use Quochao56\Student\Filament\Resources\StudentLeaveRequestResource;

class CreateStudentLeaveRequest extends CreateRecord
{
    protected static string $resource = StudentLeaveRequestResource::class;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        $recipients = User::permission('student_leave_requests.view_all')->get();
        $superAdmins = User::where('is_super_admin', true)->get();
        $allRecipients = $recipients->merge($superAdmins)
            ->unique('id')
            ->reject(fn ($user) => $user->id === auth()->id());

        $studentName = $record->student?->name ?? 'Học sinh';
        $startDate = $record->start_date ? Carbon::parse($record->start_date)->format('d/m/Y') : '';
        $sessionLabel = $record->half_day_session === 'morning' ? 'buổi sáng' : ($record->half_day_session === 'afternoon' ? 'buổi chiều' : '');
        $bodyText = $record->half_day
            ? "Đã ghi nhận đơn xin nghỉ nửa buổi (**{$sessionLabel}**) cho học sinh **{$studentName}** ngày {$startDate}."
            : "Đã ghi nhận đơn xin nghỉ cho học sinh **{$studentName}** từ ngày {$startDate}.";
        $viewUrl = StudentLeaveRequestResource::getUrl('edit', ['record' => $record->id]);

        foreach ($allRecipients as $recipient) {
            Notification::make()
                ->info()
                ->title('Đơn xin nghỉ phép học sinh mới')
                ->body($bodyText)
                ->icon('heroicon-o-information-circle')
                ->actions([
                    Action::make('view')
                        ->label('Xem chi tiết')
                        ->url($viewUrl)
                        ->button()
                        ->color('info')
                        ->markAsRead(),
                ])
                ->sendToDatabase($recipient);
        }
    }
}
