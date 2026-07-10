<?php

namespace Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Quochao56\Employee\Models\Employee;

class AttendanceCorrectionRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label(trans('packages.employee::leave_request.fields.employee_id'))
                ->relationship('employee', 'name')
                ->searchable()
                ->preload()
                ->default(fn() => Employee::where('email', auth()->user()->email)->first()?->id)
                ->disabled(fn() => ! auth()->user()->hasPermissionTo('attendance_correction_requests.view_all'))
                ->dehydrated() // ensure it is saved even when disabled
                ->required(),

            DatePicker::make('attendance_date')
                ->label(trans('packages.employee::attendance_correction_request.fields.attendance_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->maxDate(now())
                ->required(),

            DateTimePicker::make('requested_check_in_at')
                ->label(trans('packages.employee::attendance_correction_request.fields.requested_check_in_at'))
                ->native(false)
                ->displayFormat('d/m/Y H:i'),

            DateTimePicker::make('requested_check_out_at')
                ->label(trans('packages.employee::attendance_correction_request.fields.requested_check_out_at'))
                ->native(false)
                ->displayFormat('d/m/Y H:i'),

            Textarea::make('reason')
                ->label(trans('packages.employee::attendance_correction_request.fields.reason'))
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Select::make('status')
                ->label(trans('packages.employee::attendance_correction_request.fields.status'))
                ->options([
                    'pending' => trans('packages.employee::attendance_correction_request.status.pending'),
                    'approved' => trans('packages.employee::attendance_correction_request.status.approved'),
                    'rejected' => trans('packages.employee::attendance_correction_request.status.rejected'),
                ])
                ->default('pending')
                ->required()
                ->disabled(fn($record) => $record && ! $record->isPending()),

            Textarea::make('review_note')
                ->label(trans('packages.employee::attendance_correction_request.fields.review_note'))
                ->rows(2)
                ->columnSpanFull()
                ->visible(fn($record) => $record && ! $record->isPending()),
        ]);
    }
}
