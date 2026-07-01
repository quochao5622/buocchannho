<?php

namespace Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Schemas;

use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\BehaviorIntensityEnum;
use Quochao56\Student\Models\Student;

class BehaviorIncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label(trans('packages.session_log::behavior_incident.fields.student_id'))
                ->options(function () {
                    $query = Student::query()->where('status', 'active');
                    if (auth()->check() && ! auth()->user()->isSuperAdmin()) {
                        $canManageAll = auth()->user()->hasPermissionTo('employees.index')
                            || auth()->user()->hasPermissionTo('employees.edit');
                        if (! $canManageAll) {
                            $employee = Employee::where('email', auth()->user()->email)->first();
                            if ($employee) {
                                $query->whereHas('currentAssignment', function ($q) use ($employee) {
                                    $q->where('employee_id', $employee->id);
                                });
                            } else {
                                $query->whereRaw('1 = 0');
                            }
                        }
                    }

                    return $query->pluck('name', 'id')->toArray();
                })
                ->required()
                ->searchable(fn (?Model $record) => $record === null)
                ->disabled(fn (?Model $record) => $record !== null)
                ->dehydrated()
                ->selectablePlaceholder(fn (?Model $record) => $record === null),

            Hidden::make('employee_id')
                ->default(fn () => Employee::where('email', auth()->user()->email)->first()?->id ?? 4),

            DateTimePicker::make('incident_date')
                ->label(trans('packages.session_log::behavior_incident.fields.incident_date'))
                ->native(false)
                ->displayFormat('d/m/Y H:i')
                ->default(now())
                ->required(),

            Select::make('intensity')
                ->label(trans('packages.session_log::behavior_incident.fields.intensity'))
                ->options(BehaviorIntensityEnum::class)
                ->required()
                ->native(false),

            TextInput::make('duration_minutes')
                ->label(trans('packages.session_log::behavior_incident.fields.duration_minutes'))
                ->numeric()
                ->minValue(1)
                ->suffix(trans('packages.session_log::behavior_incident.units.minutes')),

            self::abcSection(
                field: 'antecedent',
                icon: Heroicon::OutlinedLightBulb,
                iconColor: 'info',
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.06)',
                maxLength: 2000,
            ),

            self::abcSection(
                field: 'behavior',
                icon: Heroicon::OutlinedExclamationTriangle,
                iconColor: 'danger',
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.06)',
                maxLength: 2000,
            ),

            self::abcSection(
                field: 'consequence',
                icon: Heroicon::OutlinedHandRaised,
                iconColor: 'success',
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34, 197, 94, 0.06)',
                maxLength: 2000,
            ),

            Textarea::make('notes')
                ->label(trans('packages.session_log::behavior_incident.fields.notes'))
                ->rows(2)
                ->live()
                ->maxLength(1000)
                ->helperText(self::characterCountHelper('notes', 1000))
                ->columnSpanFull(),
        ]);
    }

    private static function abcSection(
        string $field,
        Heroicon $icon,
        string $iconColor,
        string $borderColor,
        string $backgroundColor,
        int $maxLength,
    ): Section {
        return Section::make(trans("packages.session_log::behavior_incident.sections.{$field}.heading"))
            ->description(trans("packages.session_log::behavior_incident.sections.{$field}.description"))
            ->icon($icon)
            ->iconColor($iconColor)
            ->extraAttributes([
                'style' => "border-left: 4px solid {$borderColor}; padding: 16px 16px 16px 20px; background-color: {$backgroundColor}; margin-bottom: 32px; border-radius: 8px;",
            ])
            ->schema([
                Textarea::make($field)
                    ->hiddenLabel()
                    ->placeholder(trans("packages.session_log::behavior_incident.sections.{$field}.placeholder"))
                    ->rows(3)
                    ->live()
                    ->required()
                    ->maxLength($maxLength)
                    ->helperText(self::characterCountHelper($field, $maxLength))
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function characterCountHelper(string $field, int $maxLength): Closure
    {
        return function (Get $get) use ($field, $maxLength): string {
            $length = mb_strlen($get($field) ?? '');

            return trans('packages.session_log::behavior_incident.helpers.character_count', [
                'current' => $length,
                'max' => $maxLength,
            ]);
        };
    }
}
