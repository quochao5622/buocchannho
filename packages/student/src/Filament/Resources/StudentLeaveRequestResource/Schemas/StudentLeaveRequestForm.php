<?php

namespace Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StudentLeaveRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label(trans('packages.student::student_leave_request.fields.student_id'))
                ->relationship('student', 'name', function (Builder $query) {
                    $user = auth()->user();
                    if ($user->isSuperAdmin() || $user->hasPermissionTo('student_leave_requests.view_all')) {
                        return $query;
                    }
                    $employee = $user->employee;
                    if ($employee) {
                        return $query->whereHas('assignments', function ($q) use ($employee) {
                            $q->where('employee_id', $employee->id)
                                ->where('status', 'active');
                        });
                    }

                    return $query->whereRaw('1 = 0');
                })
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('start_date')
                ->label(trans('packages.student::student_leave_request.fields.start_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            DatePicker::make('end_date')
                ->label(trans('packages.student::student_leave_request.fields.end_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Textarea::make('reason')
                ->label(trans('packages.student::student_leave_request.fields.reason'))
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Select::make('status')
                ->label(trans('packages.student::student_leave_request.fields.status'))
                ->options([
                    'pending' => trans('packages.student::student_leave_request.status.pending'),
                    'approved' => trans('packages.student::student_leave_request.status.approved'),
                    'rejected' => trans('packages.student::student_leave_request.status.rejected'),
                ])
                ->default('pending')
                ->disabled()
                ->dehydrated(),

            Textarea::make('rejection_reason')
                ->label(trans('packages.student::student_leave_request.fields.rejection_reason'))
                ->visible(fn ($get) => $get('status') === 'rejected')
                ->disabled()
                ->dehydrated()
                ->columnSpanFull(),

            Select::make('created_by')
                ->label(trans('packages.student::student_leave_request.fields.created_by'))
                ->relationship('creator', 'name')
                ->disabled()
                ->dehydrated()
                ->visible(fn ($record) => $record !== null),
        ]);
    }
}
