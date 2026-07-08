<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Quochao56\Employee\Models\EmployeeAttendance;

class EmployeeAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label(trans('packages.employee::employee_attendance.fields.employee_id'))
                ->relationship('employee', 'name')
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('date')
                ->label(trans('packages.employee::employee_attendance.fields.date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Select::make('session')
                ->label(trans('packages.employee::employee_attendance.fields.session'))
                ->options([
                    'morning' => trans('packages.employee::employee_attendance.sessions.morning'),
                    'afternoon' => trans('packages.employee::employee_attendance.sessions.afternoon'),
                    'evening' => trans('packages.employee::employee_attendance.sessions.evening'),
                ])
                ->default('morning')
                ->required(),

            TimePicker::make('check_in_at')
                ->label(trans('packages.employee::employee_attendance.fields.check_in_at'))
                ->native(false)
                ->displayFormat('H:i:s'),

            TimePicker::make('check_out_at')
                ->label(trans('packages.employee::employee_attendance.fields.check_out_at'))
                ->native(false)
                ->displayFormat('H:i:s'),

            TextInput::make('total_hours')
                ->label(trans('packages.employee::employee_attendance.fields.total_hours'))
                ->numeric()
                ->step(0.1)
                ->nullable(),

            Select::make('status')
                ->label(trans('packages.employee::employee_attendance.fields.status'))
                ->options([
                    'present' => trans('packages.employee::employee_attendance.status.present'),
                    'late' => trans('packages.employee::employee_attendance.status.late'),
                    'early_leave' => trans('packages.employee::employee_attendance.status.early_leave'),
                    'absent' => trans('packages.employee::employee_attendance.status.absent'),
                    'on_leave' => trans('packages.employee::employee_attendance.status.on_leave'),
                ])
                ->default('present')
                ->required(),

            Section::make(trans('packages.employee::employee_attendance.fields.verification_status'))
                ->description('Thông tin liên quan đến xác minh vị trí chấm công')
                ->icon('heroicon-o-map-pin')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->visible(fn () => Auth::user()?->can('approveFlaggedLocation', EmployeeAttendance::class))
                ->schema([
                    Toggle::make('flagged_location')
                        ->label(trans('packages.employee::employee_attendance.fields.flagged_location'))
                        ->disabled()
                        ->columnSpanFull(),

                    Select::make('verification_status')
                        ->label(trans('packages.employee::employee_attendance.fields.verification_status'))
                        ->options([
                            'pending' => trans('packages.employee::employee_attendance.verification_status.pending'),
                            'approved' => trans('packages.employee::employee_attendance.verification_status.approved'),
                            'rejected' => trans('packages.employee::employee_attendance.verification_status.rejected'),
                        ])
                        ->disabled()
                        ->columnSpanFull(),

                    Placeholder::make('check_in_location')
                        ->label('Vị trí Check-in')
                        ->content(fn ($record) => $record && $record->check_in_latitude && $record->check_in_longitude
                            ? new HtmlString(sprintf(
                                '<a href="https://www.google.com/maps/search/?api=1&query=%f,%f" target="_blank" class="text-primary-600 hover:underline inline-flex items-center gap-1 font-semibold">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Xem trên Google Maps (%f, %f)
                                </a>',
                                $record->check_in_latitude,
                                $record->check_in_longitude,
                                $record->check_in_latitude,
                                $record->check_in_longitude
                            ))
                            : 'Không có dữ liệu GPS (hoặc dùng xác thực IP)')
                        ->columnSpan(1),

                    Placeholder::make('check_out_location')
                        ->label('Vị trí Check-out')
                        ->content(fn ($record) => $record && $record->check_out_latitude && $record->check_out_longitude
                            ? new HtmlString(sprintf(
                                '<a href="https://www.google.com/maps/search/?api=1&query=%f,%f" target="_blank" class="text-primary-600 hover:underline inline-flex items-center gap-1 font-semibold">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Xem trên Google Maps (%f, %f)
                                </a>',
                                $record->check_out_latitude,
                                $record->check_out_longitude,
                                $record->check_out_latitude,
                                $record->check_out_longitude
                            ))
                            : 'Không có dữ liệu GPS (hoặc dùng xác thực IP)')
                        ->columnSpan(1),
                ]),

            Textarea::make('notes')
                ->label(trans('packages.employee::employee_attendance.fields.notes'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }
}
