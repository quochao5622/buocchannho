<?php

namespace Quochao56\Employee\Filament\Resources\LeaveRequestResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Quochao56\Employee\Models\Employee;

class LeaveRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label(trans('packages.employee::leave_request.fields.employee_id'))
                ->relationship('employee', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => Employee::where('email', auth()->user()->email)->first()?->id)
                ->disabled(fn () => ! auth()->user()->hasPermissionTo('leave_requests.view_all'))
                ->dehydrated() // ensure it is saved even when disabled
                ->required(),

            Select::make('leave_type')
                ->label(trans('packages.employee::leave_request.fields.leave_type'))
                ->options([
                    'annual' => trans('packages.employee::leave_request.type.annual'),
                    'sick' => trans('packages.employee::leave_request.type.sick'),
                    'unpaid' => trans('packages.employee::leave_request.type.unpaid'),
                    'maternity' => trans('packages.employee::leave_request.type.maternity'),
                    'other' => trans('packages.employee::leave_request.type.other'),
                ])
                ->default('annual')
                ->required(),

            DatePicker::make('start_date')
                ->label(trans('packages.employee::leave_request.fields.start_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            DatePicker::make('end_date')
                ->label(trans('packages.employee::leave_request.fields.end_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Toggle::make('half_day')
                ->label(trans('packages.employee::leave_request.fields.half_day'))
                ->live()
                ->default(false),

            Select::make('half_day_session')
                ->label(trans('packages.employee::leave_request.fields.half_day_session'))
                ->options([
                    'morning' => trans('packages.employee::leave_request.fields.session_morning'),
                    'afternoon' => trans('packages.employee::leave_request.fields.session_afternoon'),
                ])
                ->visible(fn ($get) => $get('half_day'))
                ->required(fn ($get) => $get('half_day')),

            Textarea::make('reason')
                ->label(trans('packages.employee::leave_request.fields.reason'))
                ->required()
                ->rows(3)
                ->columnSpanFull(),

            Select::make('status')
                ->label(trans('packages.employee::leave_request.fields.status'))
                ->options([
                    'pending' => trans('packages.employee::leave_request.status.pending'),
                    'approved' => trans('packages.employee::leave_request.status.approved'),
                    'rejected' => trans('packages.employee::leave_request.status.rejected'),
                ])
                ->default('pending')
                ->disabled() // Status can only be changed via actions or by admin
                ->dehydrated(),

            Textarea::make('rejection_reason')
                ->label(trans('packages.employee::leave_request.fields.rejection_reason'))
                ->visible(fn ($get) => $get('status') === 'rejected')
                ->disabled()
                ->dehydrated()
                ->columnSpanFull(),
        ]);
    }
}
