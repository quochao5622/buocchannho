<?php

namespace Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Quochao56\Student\Models\StudentLeaveRequest;

class StudentLeaveRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label(trans('packages.student::student_leave_request.fields.student_id'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label(trans('packages.student::student_leave_request.fields.start_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(trans('packages.student::student_leave_request.fields.end_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(trans('packages.student::student_leave_request.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.student::student_leave_request.status.{$state}")),

                TextColumn::make('reason')
                    ->label(trans('packages.student::student_leave_request.fields.reason'))
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approver.name')
                    ->label(trans('packages.student::student_leave_request.fields.approved_by'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label(trans('packages.student::student_leave_request.fields.created_by'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label(trans('packages.student::student_leave_request.fields.student_id'))
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label(trans('packages.student::student_leave_request.fields.status'))
                    ->options([
                        'pending' => trans('packages.student::student_leave_request.status.pending'),
                        'approved' => trans('packages.student::student_leave_request.status.approved'),
                        'rejected' => trans('packages.student::student_leave_request.status.rejected'),
                    ]),
            ])
            ->actions([
                // Approval Action
                Action::make('approve')
                    ->label(trans('packages.student::student_leave_request.actions.approve'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(trans('packages.student::student_leave_request.actions.approve'))
                    ->modalDescription(trans('packages.student::student_leave_request.actions.approve_confirm'))
                    ->visible(fn (StudentLeaveRequest $record) => $record->status === 'pending' && auth()->user()->hasPermissionTo('student_leave_requests.approve'))
                    ->action(function (StudentLeaveRequest $record, $livewire) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title(trans('packages.student::student_leave_request.actions.approve_success'))
                            ->success()
                            ->send();

                        $livewire->dispatch('notificationsSent');
                    }),

                // Rejection Action
                Action::make('reject')
                    ->label(trans('packages.student::student_leave_request.actions.reject'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label(trans('packages.student::student_leave_request.fields.rejection_reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->modalHeading(trans('packages.student::student_leave_request.actions.reject'))
                    ->modalDescription(trans('packages.student::student_leave_request.actions.reject_confirm'))
                    ->visible(fn (StudentLeaveRequest $record) => $record->status === 'pending' && auth()->user()->hasPermissionTo('student_leave_requests.approve'))
                    ->action(function (StudentLeaveRequest $record, array $data, $livewire) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title(trans('packages.student::student_leave_request.actions.reject_success'))
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
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
