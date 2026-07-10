<?php

namespace Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentLeaveRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label(trans('packages.student::student_leave_request.fields.student_id'))
                ->relationship('student', 'name', function (Builder $query) {
                    $user = Auth::user();
                    if (! $user) {
                        return $query->whereRaw('1 = 0');
                    }

                    if ($user->hasPermissionTo('student_leave_requests.view_all')) {
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

            Toggle::make('half_day')
                ->label(trans('packages.student::student_leave_request.fields.half_day'))
                ->live()
                ->default(false),

            Select::make('half_day_session')
                ->label(trans('packages.student::student_leave_request.fields.half_day_session'))
                ->options([
                    'morning' => trans('packages.student::student_leave_request.fields.session_morning'),
                    'afternoon' => trans('packages.student::student_leave_request.fields.session_afternoon'),
                ])
                ->visible(fn ($get) => $get('half_day'))
                ->required(fn ($get) => $get('half_day')),

            Textarea::make('reason')
                ->label(trans('packages.student::student_leave_request.fields.reason'))
                ->required()
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }
}
