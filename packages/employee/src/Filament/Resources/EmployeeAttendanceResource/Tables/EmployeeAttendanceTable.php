<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Tables;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
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
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Quochao56\Employee\Exports\EmployeeAttendancePivotExport;
use Quochao56\Employee\Models\EmployeeAttendance;

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

                TextColumn::make('session')
                    ->label(trans('packages.employee::employee_attendance.fields.session'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'morning' => 'success',
                        'afternoon' => 'warning',
                        'evening' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.employee::employee_attendance.sessions.{$state}")),

                TextColumn::make('check_in_at')
                    ->label(trans('packages.employee::employee_attendance.fields.check_in_at'))
                    ->dateTime('H:i:s')
                    ->placeholder('-'),

                TextColumn::make('check_out_at')
                    ->label(trans('packages.employee::employee_attendance.fields.check_out_at'))
                    ->dateTime('H:i:s')
                    ->placeholder('-'),

                TextInputColumn::make('total_hours')
                    ->label(trans('packages.employee::employee_attendance.fields.total_hours'))
                    ->placeholder('-')
                    ->type('number')
                    ->step('0.01')
                    ->disabled(fn () => ! Auth::user()?->hasPermissionTo('employee_attendances.edit'))
                    ->rules(['nullable', 'numeric', 'min:0', 'max:24']),

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
                    ->visible(fn () => Auth::user()?->can('approveFlaggedLocation', EmployeeAttendance::class)),

                IconColumn::make('flagged_missing_checkin')
                    ->label(trans('packages.employee::employee_attendance.fields.flagged_missing_checkin'))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                IconColumn::make('flagged_missing_checkout')
                    ->label(trans('packages.employee::employee_attendance.fields.flagged_missing_checkout'))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                BadgeColumn::make('verification_status')
                    ->label(trans('packages.employee::employee_attendance.fields.verification_status'))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.employee::employee_attendance.verification_status.{$state}"))
                    ->visible(fn () => Auth::user()?->can('approveFlaggedLocation', EmployeeAttendance::class)),

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

                SelectFilter::make('session')
                    ->label(trans('packages.employee::employee_attendance.fields.session'))
                    ->options([
                        'morning' => trans('packages.employee::employee_attendance.sessions.morning'),
                        'afternoon' => trans('packages.employee::employee_attendance.sessions.afternoon'),
                        'evening' => trans('packages.employee::employee_attendance.sessions.evening'),
                    ]),

                TernaryFilter::make('flagged_location')
                    ->label(trans('packages.employee::employee_attendance.fields.flagged_location'))
                    ->visible(fn () => Auth::user()?->can('approveFlaggedLocation', EmployeeAttendance::class)),

                TernaryFilter::make('flagged_missing_checkin')
                    ->label(trans('packages.employee::employee_attendance.fields.flagged_missing_checkin')),

                TernaryFilter::make('flagged_missing_checkout')
                    ->label(trans('packages.employee::employee_attendance.fields.flagged_missing_checkout')),

                SelectFilter::make('verification_status')
                    ->label(trans('packages.employee::employee_attendance.fields.verification_status'))
                    ->options([
                        'pending' => trans('packages.employee::employee_attendance.verification_status.pending'),
                        'approved' => trans('packages.employee::employee_attendance.verification_status.approved'),
                        'rejected' => trans('packages.employee::employee_attendance.verification_status.rejected'),
                    ])
                    ->visible(fn () => Auth::user()?->can('approveFlaggedLocation', EmployeeAttendance::class)),

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
            ->headerActions([
                Action::make('export_pivot')
                    ->label('Xuất bảng chấm công')
                    ->icon('heroicon-o-table-cells')
                    ->color('info')
                    ->action(function ($livewire) {
                        $tableFilterState = $livewire->getTableFilterState('date_range');
                        $fromDate = $tableFilterState['from_date'] ?? null;
                        $toDate = $tableFilterState['to_date'] ?? null;

                        if (! $fromDate || ! $toDate) {
                            $fromDate = now()->startOfMonth()->toDateString();
                            $toDate = now()->endOfMonth()->toDateString();
                        }

                        $query = clone $livewire->getFilteredTableQuery();

                        if (empty($tableFilterState['from_date']) && empty($tableFilterState['to_date'])) {
                            $query->whereDate('date', '>=', $fromDate)
                                ->whereDate('date', '<=', $toDate);
                        }

                        $records = $query->with('employee')->get();

                        return ExcelFacade::download(
                            new EmployeeAttendancePivotExport($fromDate, $toDate, $records),
                            "bang-cham-cong-{$fromDate}-to-{$toDate}.xlsx"
                        );
                    }),
                ExportAction::make('export')
                    ->label('Xuất Excel')
                    ->color('success')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->modifyQueryUsing(function ($query) {
                                return $query
                                    ->selectRaw("
                                        MIN(id) as id,
                                        employee_id,
                                        date,
                                        (CASE WHEN session = 'evening' THEN 'evening' ELSE 'office' END) as session,
                                        MIN(check_in_at) as check_in_at,
                                        MAX(check_out_at) as check_out_at,
                                        SUM(total_hours) as total_hours,
                                        (CASE
                                            WHEN SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) > 0 THEN 'absent'
                                            WHEN SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) > 0 THEN 'late'
                                            WHEN SUM(CASE WHEN status = 'early_leave' THEN 1 ELSE 0 END) > 0 THEN 'early_leave'
                                            ELSE 'present'
                                         END) as status,
                                        (CASE
                                            WHEN SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) > 0 THEN 'pending'
                                            WHEN SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) > 0 THEN 'rejected'
                                            ELSE 'approved'
                                         END) as verification_status,
                                        group_concat(notes) as notes
                                    ")
                                    ->groupBy('employee_id', 'date')
                                    ->groupByRaw("(CASE WHEN session = 'evening' THEN 'evening' ELSE 'office' END)")
                                    ->reorder()
                                    ->orderBy('date', 'desc');
                            })
                            ->withWriterType(Excel::XLSX)
                            ->withFilename(fn (): string => 'cham-cong-'.now()->format('Y-m-d-H-i-s'))
                            ->withColumns([
                                Column::make('employee.name')->heading(trans('packages.employee::employee_attendance.fields.employee_id')),
                                Column::make('date')
                                    ->heading(trans('packages.employee::employee_attendance.fields.date'))
                                    ->formatStateUsing(fn ($state) => $state ? ($state instanceof Carbon ? $state : Carbon::parse($state))->format('d/m/Y') : '-'),
                                Column::make('session')
                                    ->heading(trans('packages.employee::employee_attendance.fields.session'))
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'evening' => trans('packages.employee::employee_attendance.sessions.evening'),
                                        'office' => 'Giờ hành chính (Sáng & Chiều)',
                                        default => trans("packages.employee::employee_attendance.sessions.{$state}"),
                                    }),
                                Column::make('check_in_at')
                                    ->heading(trans('packages.employee::employee_attendance.fields.check_in_at'))
                                    ->formatStateUsing(fn ($state) => $state ? ($state instanceof Carbon ? $state : Carbon::parse($state))->format('H:i:s') : '-'),
                                Column::make('check_out_at')
                                    ->heading(trans('packages.employee::employee_attendance.fields.check_out_at'))
                                    ->formatStateUsing(fn ($state) => $state ? ($state instanceof Carbon ? $state : Carbon::parse($state))->format('H:i:s') : '-'),
                                Column::make('total_hours')->heading(trans('packages.employee::employee_attendance.fields.total_hours')),
                                Column::make('status')
                                    ->heading(trans('packages.employee::employee_attendance.fields.status'))
                                    ->formatStateUsing(fn ($state) => trans("packages.employee::employee_attendance.status.{$state}")),
                                Column::make('verification_status')
                                    ->heading(trans('packages.employee::employee_attendance.fields.verification_status'))
                                    ->formatStateUsing(fn ($state) => trans("packages.employee::employee_attendance.verification_status.{$state}")),
                                Column::make('notes')->heading(trans('packages.employee::employee_attendance.fields.notes')),
                            ]),
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->label(trans('packages.employee::employee_attendance.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->flagged_location && $record->verification_status === 'pending' && Auth::user()?->can('approveFlaggedLocation', $record))
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
                    ->visible(fn ($record) => $record->flagged_location && $record->verification_status === 'pending' && Auth::user()?->can('rejectFlaggedLocation', $record))
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
                    BulkAction::make('approve_bulk')
                        ->label('Duyệt vị trí hàng loạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => Auth::user()?->can('approveFlaggedLocation', EmployeeAttendance::class))
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->flagged_location && $record->verification_status === 'pending') {
                                    $record->update(['verification_status' => 'approved']);
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                Notification::make()
                                    ->title('Đã duyệt hàng loạt thành công')
                                    ->body("Đã duyệt vị trí cho {$count} chấm công được chọn.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Không có bản ghi nào hợp lệ')
                                    ->body('Chỉ duyệt được các bản ghi chấm công có trạng thái Chờ duyệt vị trí.')
                                    ->warning()
                                    ->send();
                            }
                        }),

                    BulkAction::make('reject_bulk')
                        ->label('Từ chối vị trí hàng loạt')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn () => Auth::user()?->can('rejectFlaggedLocation', EmployeeAttendance::class))
                        ->form([
                            Textarea::make('reject_reason')
                                ->label('Lý do từ chối chung')
                                ->placeholder('Nhập lý do từ chối cho các bản ghi được chọn...')
                                ->rows(3)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->flagged_location && $record->verification_status === 'pending') {
                                    $record->update([
                                        'verification_status' => 'rejected',
                                        'notes' => ($record->notes ? $record->notes."\n" : '').'Từ chối hàng loạt: '.($data['reject_reason'] ?? 'Không có lý do'),
                                    ]);
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                Notification::make()
                                    ->title('Đã từ chối hàng loạt thành công')
                                    ->body("Đã từ chối vị trí cho {$count} chấm công được chọn.")
                                    ->danger()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Không có bản ghi nào hợp lệ')
                                    ->body('Chỉ từ chối được các bản ghi chấm công có trạng thái Chờ duyệt vị trí.')
                                    ->warning()
                                    ->send();
                            }
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('date')
                    ->label('Nhóm theo ngày')
                    ->date(),
                Group::make('employee.name')
                    ->label('Nhóm theo nhân viên'),
            ])
            ->defaultGroup('date')
            ->defaultSort('date', 'desc');
    }
}
