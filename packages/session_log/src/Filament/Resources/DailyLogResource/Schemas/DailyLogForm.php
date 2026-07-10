<?php

namespace Quochao56\SessionLog\Filament\Resources\DailyLogResource\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\DailyLogEmotionEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogRatingEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogStatusEnum;
use Quochao56\Student\Models\Student;

class DailyLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('student_id')
                ->label(trans('packages.session_log::daily_log.fields.student_id'))
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

            DatePicker::make('log_date')
                ->label(trans('packages.session_log::daily_log.fields.log_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->required(),

            Select::make('emotion')
                ->label(trans('packages.session_log::daily_log.fields.emotion'))
                ->options(DailyLogEmotionEnum::class)
                ->required(),

            Select::make('focus_level')
                ->label(trans('packages.session_log::daily_log.fields.focus_level'))
                ->options(DailyLogRatingEnum::class)
                ->required(),

            Select::make('cooperation_level')
                ->label(trans('packages.session_log::daily_log.fields.cooperation_level'))
                ->options(DailyLogRatingEnum::class)
                ->required(),

            self::limitedTextarea('eating_note'),

            self::limitedTextarea('sleeping_note'),

            self::limitedTextarea('hygiene_note'),

            self::limitedTextarea('general_note')
                ->columnSpanFull(),

            Select::make('status')
                ->label(trans('packages.session_log::daily_log.fields.status'))
                ->options(DailyLogStatusEnum::class)
                ->default(DailyLogStatusEnum::Draft->value)
                ->required(),

            Checkbox::make('send_notification')
                ->label(trans('packages.session_log::daily_log.fields.send_notification'))
                ->disabled()
                ->dehydrated(false)
                ->columnSpanFull(),
        ]);
    }

    private static function limitedTextarea(string $field, int $maxLength = 2000): Textarea
    {
        return Textarea::make($field)
            ->label(trans("packages.session_log::daily_log.fields.{$field}"))
            ->rows(3)
            ->live()
            ->maxLength($maxLength)
            ->helperText(function (Get $get) use ($field, $maxLength): string {
                $length = mb_strlen($get($field) ?? '');

                return trans('packages.session_log::daily_log.helpers.character_count', [
                    'current' => $length,
                    'max' => $maxLength,
                ]);
            });
    }
}
