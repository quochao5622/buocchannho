<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAttendanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label(trans('packages.employee::employee_attendance.fields.employee_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label(trans('packages.employee::employee_attendance.fields.date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('check_in_at')
                    ->label(trans('packages.employee::employee_attendance.fields.check_in_at'))
                    ->dateTime('H:i:s')
                    ->placeholder('-'),

                TextColumn::make('check_out_at')
                    ->label(trans('packages.employee::employee_attendance.fields.check_out_at'))
                    ->dateTime('H:i:s')
                    ->placeholder('-'),

                TextColumn::make('total_hours')
                    ->label(trans('packages.employee::employee_attendance.fields.total_hours'))
                    ->placeholder('-')
                    ->suffix('h'),

                TextColumn::make('status')
                    ->label(trans('packages.employee::employee_attendance.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'early_leave' => 'info',
                        'absent' => 'danger',
                        'on_leave' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.employee::employee_attendance.status.{$state}")),

                IconColumn::make('flagged_location')
                    ->label(trans('packages.employee::employee_attendance.fields.flagged_location'))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->visible(fn () => auth()->user()?->can('approve_flagged_location', 'employee_attendances') || auth()->user()?->isSuperAdmin()),

                BadgeColumn::make('verification_status')
                    ->label(trans('packages.employee::employee_attendance.fields.verification_status'))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.employee::employee_attendance.verification_status.{$state}"))
                    ->visible(fn () => auth()->user()?->can('approve_flagged_location', 'employee_attendances') || auth()->user()?->isSuperAdmin()),

                TextColumn::make('check_in_ip')
                    ->label(trans('packages.employee::employee_attendance.fields.check_in_ip'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label(trans('packages.employee::employee_attendance.fields.notes'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label(trans('packages.employee::employee_attendance.fields.employee_id'))
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label(trans('packages.employee::employee_attendance.fields.status'))
                    ->options([
                        'present' => trans('packages.employee::employee_attendance.status.present'),
                        'late' => trans('packages.employee::employee_attendance.status.late'),
                        'early_leave' => trans('packages.employee::employee_attendance.status.early_leave'),
                        'absent' => trans('packages.employee::employee_attendance.status.absent'),
                        'on_leave' => trans('packages.employee::employee_attendance.status.on_leave'),
                    ]),

                TernaryFilter::make('flagged_location')
                    ->label(trans('packages.employee::employee_attendance.fields.flagged_location'))
                    ->visible(fn () => auth()->user()?->can('approve_flagged_location', 'employee_attendances') || auth()->user()?->isSuperAdmin()),

                SelectFilter::make('verification_status')
                    ->label(trans('packages.employee::employee_attendance.fields.verification_status'))
                    ->options([
                        'pending' => trans('packages.employee::employee_attendance.verification_status.pending'),
                        'approved' => trans('packages.employee::employee_attendance.verification_status.approved'),
                        'rejected' => trans('packages.employee::employee_attendance.verification_status.rejected'),
                    ])
                    ->visible(fn () => auth()->user()?->can('approve_flagged_location', 'employee_attendances') || auth()->user()?->isSuperAdmin()),

                Filter::make('date_range')
                    ->label('Khoảng ngày')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('Từ ngày')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('to_date')
                            ->label('Đến ngày')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Action::make('approve')
                    ->label(trans('packages.employee::employee_attendance.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->flagged_location && $record->verification_status === 'pending' && (auth()->user()?->can('approve_flagged_location', 'employee_attendances') || auth()->user()?->isSuperAdmin()))
                    ->action(function ($record, $livewire) {
                        $record->update([
                            'verification_status' => 'approved',
                        ]);
                        Notification::make()
                            ->title('Đã duyệt chấm công')
                            ->body("Điểm danh của {$record->employee->name} ngày {$record->date->format('d/m/Y')} đã được duyệt.")
                            ->success()
                            ->send();

                        $livewire->dispatch('notificationsSent');
                    }),

                Action::make('reject')
                    ->label(trans('packages.employee::employee_attendance.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->flagged_location && $record->verification_status === 'pending' && (auth()->user()?->can('reject_flagged_location', 'employee_attendances') || auth()->user()?->isSuperAdmin()))
                    ->form([
                        Textarea::make('reject_reason')
                            ->label('Lý do từ chối')
                            ->placeholder('Nhập lý do từ chối chấm công này...')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        $record->update([
                            'verification_status' => 'rejected',
                            'notes' => ($record->notes ? $record->notes.'\n' : '').'Từ chối: '.($data['reject_reason'] ?? 'Không có lý do'),
                        ]);
                        Notification::make()
                            ->title('Đã từ chối chấm công')
                            ->body("Điểm danh của {$record->employee->name} ngày {$record->date->format('d/m/Y')} đã bị từ chối.")
                            ->danger()
                            ->send();

                        $livewire->dispatch('notificationsSent');
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
