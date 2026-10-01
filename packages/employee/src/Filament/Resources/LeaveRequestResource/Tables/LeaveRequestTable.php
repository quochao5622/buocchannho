<?php

namespace Quochao56\Employee\Filament\Resources\LeaveRequestResource\Tables;

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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Models\LeaveRequest;

class LeaveRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label(trans('packages.employee::leave_request.fields.employee_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('leave_type')
                    ->label(trans('packages.employee::leave_request.fields.leave_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'annual' => 'success',
                        'sick' => 'warning',
                        'unpaid' => 'danger',
                        'maternity' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.employee::leave_request.type.{$state}")),

                TextColumn::make('start_date')
                    ->label(trans('packages.employee::leave_request.fields.start_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(trans('packages.employee::leave_request.fields.end_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('half_day')
                    ->label(trans('packages.employee::leave_request.fields.half_day'))
                    ->boolean(),

                TextColumn::make('half_day_session')
                    ->label(trans('packages.employee::leave_request.fields.half_day_session'))
                    ->formatStateUsing(fn (?string $state): string => $state ? trans("packages.employee::leave_request.fields.session_{$state}") : '-')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label(trans('packages.employee::leave_request.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.employee::leave_request.status.{$state}")),

                TextColumn::make('reason')
                    ->label(trans('packages.employee::leave_request.fields.reason'))
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approver.name')
                    ->label(trans('packages.employee::leave_request.fields.approved_by'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label(trans('packages.employee::leave_request.fields.employee_id'))
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label(trans('packages.employee::leave_request.fields.status'))
                    ->options([
                        'pending' => trans('packages.employee::leave_request.status.pending'),
                        'approved' => trans('packages.employee::leave_request.status.approved'),
                        'rejected' => trans('packages.employee::leave_request.status.rejected'),
                    ]),

                Filter::make('date_range')
                    ->label('Khoảng ngày nghỉ')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // Approval Action
                Action::make('approve')
                    ->label(trans('packages.employee::leave_request.actions.approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(trans('packages.employee::leave_request.actions.approve'))
                    ->modalDescription(trans('packages.employee::leave_request.actions.approve_confirm'))
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending' && auth()->user()->hasPermissionTo('leave_requests.approve'))
                    ->action(function (LeaveRequest $record, $livewire) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        // Automatically generate attendance records with status 'on_leave'
                        $start = Carbon::parse($record->start_date);
                        $end = Carbon::parse($record->end_date);

                        for ($date = clone $start; $date->lte($end); $date->addDay()) {
                            EmployeeAttendance::updateOrCreate(
                                [
                                    'employee_id' => $record->employee_id,
                                    'date' => $date->toDateString(),
                                ],
                                [
                                    'status' => 'on_leave',
                                    'notes' => 'Nghỉ phép: '.$record->reason,
                                ]
                            );
                        }

                        Notification::make()
                            ->title(trans('packages.employee::leave_request.actions.approve_success'))
                            ->success()
                            ->send();

                        $livewire->dispatch('notificationsSent');
                    }),

                // Rejection Action
                Action::make('reject')
                    ->label(trans('packages.employee::leave_request.actions.reject'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(trans('packages.employee::leave_request.fields.rejection_reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->modalHeading(trans('packages.employee::leave_request.actions.reject'))
                    ->modalDescription(trans('packages.employee::leave_request.actions.reject_confirm'))
                    ->visible(fn (LeaveRequest $record) => $record->status === 'pending' && auth()->user()->hasPermissionTo('leave_requests.approve'))
                    ->action(function (LeaveRequest $record, array $data, $livewire) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title(trans('packages.employee::leave_request.actions.reject_success'))
                            ->success()
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
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->hasPermissionTo('leave_requests.approve'))
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    continue;
                                }
                                $record->update([
                                    'status' => 'approved',
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                ]);

                                $start = Carbon::parse($record->start_date);
                                $end = Carbon::parse($record->end_date);

                                for ($date = clone $start; $date->lte($end); $date->addDay()) {
                                    EmployeeAttendance::updateOrCreate(
                                        [
                                            'employee_id' => $record->employee_id,
                                            'date' => $date->toDateString(),
                                        ],
                                        [
                                            'status' => 'on_leave',
                                            'notes' => 'Nghỉ phép (Duyệt hàng loạt): '.$record->reason,
                                        ]
                                    );
                                }
                            }

                            Notification::make()
                                ->title('Đã duyệt các đơn xin nghỉ phép đã chọn')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('reject_bulk')
                        ->label('Từ chối hàng loạt')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->form([
                            Textarea::make('rejection_reason')
                                ->label(trans('packages.employee::leave_request.fields.rejection_reason'))
                                ->required()
                                ->rows(3),
                        ])
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->hasPermissionTo('leave_requests.approve'))
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    continue;
                                }
                                $record->update([
                                    'status' => 'rejected',
                                    'rejection_reason' => $data['rejection_reason'],
                                    'approved_by' => auth()->id(),
                                    'approved_at' => now(),
                                ]);
                            }

                            Notification::make()
                                ->title('Đã từ chối các đơn xin nghỉ phép đã chọn')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
