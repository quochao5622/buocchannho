<?php

namespace Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Quochao56\Employee\Models\AttendanceCorrectionRequest;
use Quochao56\Employee\Models\EmployeeAttendance;

class AttendanceCorrectionRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label(trans('packages.employee::attendance_correction_request.fields.employee_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendance_date')
                    ->label(trans('packages.employee::attendance_correction_request.fields.attendance_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('requested_check_in_at')
                    ->label(trans('packages.employee::attendance_correction_request.fields.requested_check_in_at'))
                    ->dateTime('H:i d/m/Y')
                    ->placeholder('—'),

                TextColumn::make('requested_check_out_at')
                    ->label(trans('packages.employee::attendance_correction_request.fields.requested_check_out_at'))
                    ->dateTime('H:i d/m/Y')
                    ->placeholder('—'),

                TextColumn::make('reason')
                    ->label(trans('packages.employee::attendance_correction_request.fields.reason'))
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->reason),

                TextColumn::make('status')
                    ->label(trans('packages.employee::attendance_correction_request.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.employee::attendance_correction_request.status.{$state}")),

                TextColumn::make('reviewedBy.name')
                    ->label(trans('packages.employee::attendance_correction_request.fields.reviewed_by'))
                    ->placeholder('—'),

                TextColumn::make('reviewed_at')
                    ->label(trans('packages.employee::attendance_correction_request.fields.reviewed_at'))
                    ->dateTime('H:i d/m/Y')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Ngày gửi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('packages.employee::attendance_correction_request.fields.status'))
                    ->options([
                        'pending' => trans('packages.employee::attendance_correction_request.status.pending'),
                        'approved' => trans('packages.employee::attendance_correction_request.status.approved'),
                        'rejected' => trans('packages.employee::attendance_correction_request.status.rejected'),
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Action::make('approve')
                    ->label(trans('packages.employee::attendance_correction_request.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(trans('packages.employee::attendance_correction_request.actions.approve_confirm'))
                    ->visible(fn (AttendanceCorrectionRequest $record) => $record->isPending())
                    ->action(function (AttendanceCorrectionRequest $record, $livewire) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $requestedCheckIn = $record->requested_check_in_at ? Carbon::parse($record->requested_check_in_at) : null;
                        $requestedCheckOut = $record->requested_check_out_at ? Carbon::parse($record->requested_check_out_at) : null;

                        if ($requestedCheckIn && $requestedCheckOut) {
                            $checkInSession = EmployeeAttendance::resolveSessionForDateTime($requestedCheckIn);
                            $checkoutSession = EmployeeAttendance::resolveSessionForDateTime($requestedCheckOut);
                            $dateStr = Carbon::parse($record->attendance_date)->toDateString();

                            if ($checkInSession === EmployeeAttendance::SESSION_MORNING && $checkoutSession === EmployeeAttendance::SESSION_AFTERNOON) {
                                // Sáng -> Chiều
                                $morningStartStr = settings('office_morning_start', '08:00');
                                $morningStart = Carbon::parse($dateStr.' '.$morningStartStr);
                                $effectiveCheckIn = $requestedCheckIn->copy()->max($morningStart);

                                $morningEndStr = settings('office_morning_end', '12:00');
                                $morningEnd = Carbon::parse($dateStr.' '.$morningEndStr);
                                $morningHours = min(4.0, max(0.0, round(abs($morningEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

                                EmployeeAttendance::updateOrCreate(
                                    ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_MORNING],
                                    [
                                        'check_in_at' => $requestedCheckIn,
                                        'check_out_at' => $morningEnd,
                                        'total_hours' => $morningHours,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'auto_closed' => true,
                                        'notes' => 'Hệ thống tự động checkout cuối ca sáng (duyệt sửa công)',
                                    ]
                                );

                                $afternoonStartStr = settings('office_afternoon_start', '13:30');
                                $afternoonEndStr = settings('office_afternoon_end', '17:30');
                                $afternoonStart = Carbon::parse($dateStr.' '.$afternoonStartStr);
                                $afternoonEnd = Carbon::parse($dateStr.' '.$afternoonEndStr);
                                $effectiveCheckOut = $requestedCheckOut->copy()->min($afternoonEnd);
                                $afternoonHours = min(4.0, max(0.0, round(abs($effectiveCheckOut->diffInMinutes($afternoonStart)) / 60, 2)));

                                EmployeeAttendance::updateOrCreate(
                                    ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_AFTERNOON],
                                    [
                                        'check_in_at' => $afternoonStart,
                                        'check_out_at' => $requestedCheckOut,
                                        'total_hours' => $afternoonHours,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'notes' => 'Hệ thống tự động check-in ca chiều (duyệt sửa công)',
                                    ]
                                );
                            } elseif ($checkInSession === EmployeeAttendance::SESSION_MORNING && $checkoutSession === EmployeeAttendance::SESSION_EVENING) {
                                // Sáng -> Tối
                                $morningStartStr = settings('office_morning_start', '08:00');
                                $morningStart = Carbon::parse($dateStr.' '.$morningStartStr);
                                $effectiveCheckIn = $requestedCheckIn->copy()->max($morningStart);

                                $morningEndStr = settings('office_morning_end', '12:00');
                                $morningEnd = Carbon::parse($dateStr.' '.$morningEndStr);
                                $morningHours = min(4.0, max(0.0, round(abs($morningEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

                                EmployeeAttendance::updateOrCreate(
                                    ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_MORNING],
                                    [
                                        'check_in_at' => $requestedCheckIn,
                                        'check_out_at' => $morningEnd,
                                        'total_hours' => $morningHours,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'auto_closed' => true,
                                        'notes' => 'Hệ thống tự động checkout cuối ca sáng (duyệt sửa công)',
                                    ]
                                );

                                $afternoonStartStr = settings('office_afternoon_start', '13:30');
                                $afternoonEndStr = settings('office_afternoon_end', '17:30');
                                $afternoonStart = Carbon::parse($dateStr.' '.$afternoonStartStr);
                                $afternoonEnd = Carbon::parse($dateStr.' '.$afternoonEndStr);
                                $afternoonHours = min(4.0, max(0.0, round(abs($afternoonEnd->diffInMinutes($afternoonStart)) / 60, 2)));

                                EmployeeAttendance::updateOrCreate(
                                    ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_AFTERNOON],
                                    [
                                        'check_in_at' => $afternoonStart,
                                        'check_out_at' => $afternoonEnd,
                                        'total_hours' => $afternoonHours,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'auto_closed' => true,
                                        'notes' => 'Hệ thống tự động chấm công ca chiều (duyệt sửa công)',
                                    ]
                                );

                                $eveningStartStr = settings('office_evening_start', '18:00');
                                $eveningStart = Carbon::parse($dateStr.' '.$eveningStartStr);
                                $eveningHours = class_exists(Schedule::class)
                                    ? Schedule::getEveningTeachingHoursForEmployeeOnDate($record->employee_id, Carbon::parse($record->attendance_date))
                                    : 0.0;

                                EmployeeAttendance::updateOrCreate(
                                    ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_EVENING],
                                    [
                                        'check_in_at' => $eveningStart,
                                        'check_out_at' => $requestedCheckOut,
                                        'total_hours' => $eveningHours,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'notes' => 'Hệ thống tự động check-in ca tối (duyệt sửa công)',
                                    ]
                                );
                            } elseif ($checkInSession === EmployeeAttendance::SESSION_AFTERNOON && $checkoutSession === EmployeeAttendance::SESSION_EVENING) {
                                // Chiều -> Tối
                                $afternoonStartStr = settings('office_afternoon_start', '13:30');
                                $afternoonStart = Carbon::parse($dateStr.' '.$afternoonStartStr);
                                $effectiveCheckIn = $requestedCheckIn->copy()->max($afternoonStart);

                                $afternoonEndStr = settings('office_afternoon_end', '17:30');
                                $afternoonEnd = Carbon::parse($dateStr.' '.$afternoonEndStr);
                                $afternoonHours = min(4.0, max(0.0, round(abs($afternoonEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

                                EmployeeAttendance::updateOrCreate(
                                    ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_AFTERNOON],
                                    [
                                        'check_in_at' => $requestedCheckIn,
                                        'check_out_at' => $afternoonEnd,
                                        'total_hours' => $afternoonHours,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'auto_closed' => true,
                                        'notes' => 'Hệ thống tự động checkout cuối ca chiều (duyệt sửa công)',
                                    ]
                                );

                                $eveningStartStr = settings('office_evening_start', '18:00');
                                $eveningStart = Carbon::parse($dateStr.' '.$eveningStartStr);
                                $eveningHours = class_exists(Schedule::class)
                                    ? Schedule::getEveningTeachingHoursForEmployeeOnDate($record->employee_id, Carbon::parse($record->attendance_date))
                                    : 0.0;

                                EmployeeAttendance::updateOrCreate(
                                    ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_EVENING],
                                    [
                                        'check_in_at' => $eveningStart,
                                        'check_out_at' => $requestedCheckOut,
                                        'total_hours' => $eveningHours,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'notes' => 'Hệ thống tự động check-in ca tối (duyệt sửa công)',
                                    ]
                                );
                            } else {
                                // Cùng ca hoặc các ca khác
                                $session = EmployeeAttendance::resolveSessionForDateTime($requestedCheckIn);
                                $totalHours = EmployeeAttendance::calculateTotalHours(
                                    $record->employee_id,
                                    Carbon::parse($record->attendance_date),
                                    $requestedCheckIn,
                                    $requestedCheckOut,
                                    $session
                                );

                                EmployeeAttendance::updateOrCreate(
                                    [
                                        'employee_id' => $record->employee_id,
                                        'date' => $record->attendance_date,
                                        'session' => $session,
                                    ],
                                    [
                                        'check_in_at' => $requestedCheckIn,
                                        'check_out_at' => $requestedCheckOut,
                                        'corrected_by' => auth()->id(),
                                        'corrected_at' => now(),
                                        'status' => 'present',
                                        'total_hours' => $totalHours,
                                    ]
                                );
                            }
                        }

                        Notification::make()
                            ->title(trans('packages.employee::attendance_correction_request.actions.approve_success'))
                            ->success()
                            ->send();

                        $livewire->dispatch('notificationsSent');
                    }),

                Action::make('reject')
                    ->label(trans('packages.employee::attendance_correction_request.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(trans('packages.employee::attendance_correction_request.actions.reject_confirm'))
                    ->form([
                        Textarea::make('review_note')
                            ->label(trans('packages.employee::attendance_correction_request.actions.review_note_label'))
                            ->rows(2),
                    ])
                    ->visible(fn (AttendanceCorrectionRequest $record) => $record->isPending())
                    ->action(function (AttendanceCorrectionRequest $record, array $data, $livewire) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_note' => $data['review_note'] ?? null,
                        ]);

                        Notification::make()
                            ->title(trans('packages.employee::attendance_correction_request.actions.reject_success'))
                            ->warning()
                            ->send();

                        $livewire->dispatch('notificationsSent');
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve_bulk')
                        ->label('Duyệt hàng loạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if (! $record->isPending()) {
                                    continue;
                                }

                                $record->update([
                                    'status' => 'approved',
                                    'reviewed_by' => auth()->id(),
                                    'reviewed_at' => now(),
                                ]);

                                $requestedCheckIn = $record->requested_check_in_at ? Carbon::parse($record->requested_check_in_at) : null;
                                $requestedCheckOut = $record->requested_check_out_at ? Carbon::parse($record->requested_check_out_at) : null;

                                if ($requestedCheckIn && $requestedCheckOut) {
                                    $checkInSession = EmployeeAttendance::resolveSessionForDateTime($requestedCheckIn);
                                    $checkoutSession = EmployeeAttendance::resolveSessionForDateTime($requestedCheckOut);
                                    $dateStr = Carbon::parse($record->attendance_date)->toDateString();

                                    if ($checkInSession === EmployeeAttendance::SESSION_MORNING && $checkoutSession === EmployeeAttendance::SESSION_AFTERNOON) {
                                        // Sáng -> Chiều
                                        $morningStartStr = settings('office_morning_start', '08:00');
                                        $morningStart = Carbon::parse($dateStr.' '.$morningStartStr);
                                        $effectiveCheckIn = $requestedCheckIn->copy()->max($morningStart);

                                        $morningEndStr = settings('office_morning_end', '12:00');
                                        $morningEnd = Carbon::parse($dateStr.' '.$morningEndStr);
                                        $morningHours = min(4.0, max(0.0, round(abs($morningEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

                                        EmployeeAttendance::updateOrCreate(
                                            ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_MORNING],
                                            [
                                                'check_in_at' => $requestedCheckIn,
                                                'check_out_at' => $morningEnd,
                                                'total_hours' => $morningHours,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'auto_closed' => true,
                                                'notes' => 'Hệ thống tự động checkout cuối ca sáng (duyệt sửa công)',
                                            ]
                                        );

                                        $afternoonStartStr = settings('office_afternoon_start', '13:30');
                                        $afternoonEndStr = settings('office_afternoon_end', '17:30');
                                        $afternoonStart = Carbon::parse($dateStr.' '.$afternoonStartStr);
                                        $afternoonEnd = Carbon::parse($dateStr.' '.$afternoonEndStr);
                                        $effectiveCheckOut = $requestedCheckOut->copy()->min($afternoonEnd);
                                        $afternoonHours = min(4.0, max(0.0, round(abs($effectiveCheckOut->diffInMinutes($afternoonStart)) / 60, 2)));

                                        EmployeeAttendance::updateOrCreate(
                                            ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_AFTERNOON],
                                            [
                                                'check_in_at' => $afternoonStart,
                                                'check_out_at' => $requestedCheckOut,
                                                'total_hours' => $afternoonHours,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'notes' => 'Hệ thống tự động check-in ca chiều (duyệt sửa công)',
                                            ]
                                        );
                                    } elseif ($checkInSession === EmployeeAttendance::SESSION_MORNING && $checkoutSession === EmployeeAttendance::SESSION_EVENING) {
                                        // Sáng -> Tối
                                        $morningStartStr = settings('office_morning_start', '08:00');
                                        $morningStart = Carbon::parse($dateStr.' '.$morningStartStr);
                                        $effectiveCheckIn = $requestedCheckIn->copy()->max($morningStart);

                                        $morningEndStr = settings('office_morning_end', '12:00');
                                        $morningEnd = Carbon::parse($dateStr.' '.$morningEndStr);
                                        $morningHours = min(4.0, max(0.0, round(abs($morningEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

                                        EmployeeAttendance::updateOrCreate(
                                            ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_MORNING],
                                            [
                                                'check_in_at' => $requestedCheckIn,
                                                'check_out_at' => $morningEnd,
                                                'total_hours' => $morningHours,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'auto_closed' => true,
                                                'notes' => 'Hệ thống tự động checkout cuối ca sáng (duyệt sửa công)',
                                            ]
                                        );

                                        $afternoonStartStr = settings('office_afternoon_start', '13:30');
                                        $afternoonEndStr = settings('office_afternoon_end', '17:30');
                                        $afternoonStart = Carbon::parse($dateStr.' '.$afternoonStartStr);
                                        $afternoonEnd = Carbon::parse($dateStr.' '.$afternoonEndStr);
                                        $afternoonHours = min(4.0, max(0.0, round(abs($afternoonEnd->diffInMinutes($afternoonStart)) / 60, 2)));

                                        EmployeeAttendance::updateOrCreate(
                                            ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_AFTERNOON],
                                            [
                                                'check_in_at' => $afternoonStart,
                                                'check_out_at' => $afternoonEnd,
                                                'total_hours' => $afternoonHours,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'auto_closed' => true,
                                                'notes' => 'Hệ thống tự động chấm công ca chiều (duyệt sửa công)',
                                            ]
                                        );

                                        $eveningStartStr = settings('office_evening_start', '18:00');
                                        $eveningStart = Carbon::parse($dateStr.' '.$eveningStartStr);
                                        $eveningHours = class_exists(\Quochao56\Scheduler\Models\Schedule::class)
                                            ? \Quochao56\Scheduler\Models\Schedule::getEveningTeachingHoursForEmployeeOnDate($record->employee_id, Carbon::parse($record->attendance_date))
                                            : 0.0;

                                        EmployeeAttendance::updateOrCreate(
                                            ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_EVENING],
                                            [
                                                'check_in_at' => $eveningStart,
                                                'check_out_at' => $requestedCheckOut,
                                                'total_hours' => $eveningHours,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'notes' => 'Hệ thống tự động check-in ca tối (duyệt sửa công)',
                                            ]
                                        );
                                    } elseif ($checkInSession === EmployeeAttendance::SESSION_AFTERNOON && $checkoutSession === EmployeeAttendance::SESSION_EVENING) {
                                        // Chiều -> Tối
                                        $afternoonStartStr = settings('office_afternoon_start', '13:30');
                                        $afternoonStart = Carbon::parse($dateStr.' '.$afternoonStartStr);
                                        $effectiveCheckIn = $requestedCheckIn->copy()->max($afternoonStart);

                                        $afternoonEndStr = settings('office_afternoon_end', '17:30');
                                        $afternoonEnd = Carbon::parse($dateStr.' '.$afternoonEndStr);
                                        $afternoonHours = min(4.0, max(0.0, round(abs($afternoonEnd->diffInMinutes($effectiveCheckIn)) / 60, 2)));

                                        EmployeeAttendance::updateOrCreate(
                                            ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_AFTERNOON],
                                            [
                                                'check_in_at' => $requestedCheckIn,
                                                'check_out_at' => $afternoonEnd,
                                                'total_hours' => $afternoonHours,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'auto_closed' => true,
                                                'notes' => 'Hệ thống tự động checkout cuối ca chiều (duyệt sửa công)',
                                            ]
                                        );

                                        $eveningStartStr = settings('office_evening_start', '18:00');
                                        $eveningStart = Carbon::parse($dateStr.' '.$eveningStartStr);
                                        $eveningHours = class_exists(\Quochao56\Scheduler\Models\Schedule::class)
                                            ? \Quochao56\Scheduler\Models\Schedule::getEveningTeachingHoursForEmployeeOnDate($record->employee_id, Carbon::parse($record->attendance_date))
                                            : 0.0;

                                        EmployeeAttendance::updateOrCreate(
                                            ['employee_id' => $record->employee_id, 'date' => $record->attendance_date, 'session' => EmployeeAttendance::SESSION_EVENING],
                                            [
                                                'check_in_at' => $eveningStart,
                                                'check_out_at' => $requestedCheckOut,
                                                'total_hours' => $eveningHours,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'notes' => 'Hệ thống tự động check-in ca tối (duyệt sửa công)',
                                            ]
                                        );
                                    } else {
                                        // Cùng ca hoặc các ca khác
                                        $session = EmployeeAttendance::resolveSessionForDateTime($requestedCheckIn);
                                        $totalHours = EmployeeAttendance::calculateTotalHours(
                                            $record->employee_id,
                                            Carbon::parse($record->attendance_date),
                                            $requestedCheckIn,
                                            $requestedCheckOut,
                                            $session
                                        );

                                        EmployeeAttendance::updateOrCreate(
                                            [
                                                'employee_id' => $record->employee_id,
                                                'date' => $record->attendance_date,
                                                'session' => $session,
                                            ],
                                            [
                                                'check_in_at' => $requestedCheckIn,
                                                'check_out_at' => $requestedCheckOut,
                                                'corrected_by' => auth()->id(),
                                                'corrected_at' => now(),
                                                'status' => 'present',
                                                'total_hours' => $totalHours,
                                            ]
                                        );
                                    }
                                }
                            }

                            Notification::make()
                                ->title('Đã phê duyệt các yêu cầu sửa công đã chọn')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('reject_bulk')
                        ->label('Từ chối hàng loạt')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('review_note')
                                ->label(trans('packages.employee::attendance_correction_request.actions.review_note_label'))
                                ->rows(2),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                if (! $record->isPending()) {
                                    continue;
                                }

                                $record->update([
                                    'status' => 'rejected',
                                    'reviewed_by' => auth()->id(),
                                    'reviewed_at' => now(),
                                    'review_note' => $data['review_note'] ?? null,
                                ]);
                            }

                            Notification::make()
                                ->title('Đã từ chối các yêu cầu sửa công đã chọn')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
