<?php

namespace Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

            Textarea::make('reason')
                ->label(trans('packages.student::student_leave_request.fields.reason'))
                ->required()
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }
}
