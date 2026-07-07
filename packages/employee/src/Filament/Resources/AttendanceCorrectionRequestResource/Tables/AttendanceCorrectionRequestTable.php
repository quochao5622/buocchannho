<?php

namespace Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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

                        // Cập nhật bản ghi employee_attendances tương ứng
                        EmployeeAttendance::updateOrCreate(
                            [
                                'employee_id' => $record->employee_id,
                                'date' => $record->attendance_date,
                            ],
                            [
                                'check_in_at' => $record->requested_check_in_at,
                                'check_out_at' => $record->requested_check_out_at,
                                'corrected_by' => auth()->id(),
                                'corrected_at' => now(),
                                'status' => 'present',
                            ]
                        );

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
            ->defaultSort('created_at', 'desc');
    }
}
