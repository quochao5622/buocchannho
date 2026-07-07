<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\RelationManagers;

use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Scheduler\Models\Schedule;

class ExceptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'exceptions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('exception_date')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.exception_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(now())
                ->live()
                ->required(),

            Select::make('action')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.action'))
                ->options([
                    'cancel' => trans('packages.scheduler::scheduler.exceptions.action.cancel'),
                    'reschedule' => trans('packages.scheduler::scheduler.exceptions.action.reschedule'),
                    'substitute' => trans('packages.scheduler::scheduler.exceptions.action.substitute'),
                ])
                ->default('cancel')
                ->live()
                ->required(),

            Select::make('new_employee_id')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_employee_id'))
                ->options(Employee::active()->pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('action') === 'substitute')
                ->required(fn (Get $get) => $get('action') === 'substitute'),

            TimePicker::make('new_start_time')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_start_time'))
                ->native(false)
                ->displayFormat('H:i:s')
                ->format('H:i:s')
                ->seconds(true)
                ->visible(fn (Get $get) => $get('action') === 'reschedule')
                ->required(fn (Get $get) => $get('action') === 'reschedule'),

            TimePicker::make('new_end_time')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_end_time'))
                ->native(false)
                ->displayFormat('H:i:s')
                ->format('H:i:s')
                ->seconds(true)
                ->visible(fn (Get $get) => $get('action') === 'reschedule')
                ->required(fn (Get $get) => $get('action') === 'reschedule'),

            Select::make('new_classroom_id')
                ->label('Phòng học mới')
                ->options(Classroom::active()->pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => $get('action') === 'reschedule')
                ->nullable(),

            Select::make('make_up_suggestion')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.make_up_suggestion'))
                ->options(fn (Get $get) => $this->getMakeUpSuggestionOptions($get('exception_date')))
                ->helperText(trans('packages.scheduler::scheduler.exceptions.fields.make_up_suggestion_help'))
                ->searchable()
                ->dehydrated(false)
                ->visible(fn (Get $get) => $get('action') === 'cancel')
                ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                    if (! $state) {
                        return;
                    }

                    [$date, $start, $end] = explode('|', $state);
                    $suggestion = "Gợi ý bù ca: {$date} {$start}-{$end}";
                    $currentNotes = trim((string) ($get('notes') ?? ''));

                    if ($currentNotes === '') {
                        $set('notes', $suggestion);

                        return;
                    }

                    if (! str_contains($currentNotes, $suggestion)) {
                        $set('notes', $currentNotes."\n".$suggestion);
                    }
                }),

            Textarea::make('reason')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.reason'))
                ->rows(2)
                ->required()
                ->columnSpanFull(),

            Textarea::make('notes')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.notes'))
                ->rows(2)
                ->nullable()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('exception_date')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.exception_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('action')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cancel' => 'danger',
                        'reschedule' => 'warning',
                        'substitute' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => trans("packages.scheduler::scheduler.exceptions.action.{$state}")),

                TextColumn::make('newEmployee.name')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_employee_id'))
                    ->placeholder('-'),

                TextColumn::make('new_start_time')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_start_time'))
                    ->time('H:i:s')
                    ->placeholder('-'),

                TextColumn::make('new_end_time')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_end_time'))
                    ->time('H:i:s')
                    ->placeholder('-'),

                TextColumn::make('newClassroom.name')
                    ->label('Phòng học mới')
                    ->placeholder('-'),

                TextColumn::make('reason')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.reason'))
                    ->limit(50),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    protected function getMakeUpSuggestionOptions(?string $exceptionDate): array
    {
        if (! $exceptionDate) {
            return [];
        }

        /** @var Schedule|null $schedule */
        $schedule = $this->getOwnerRecord();
        if (! $schedule) {
            return [];
        }

        $teacherId = (int) $schedule->employee_id;
        $studentId = (int) $schedule->student_id;
        $classroomId = $schedule->classroom_id ? (int) $schedule->classroom_id : null;
        $duration = Carbon::parse($schedule->start_time)->diffInMinutes(Carbon::parse($schedule->end_time));

        $suggestions = [];
        $startDate = Carbon::parse($exceptionDate)->addDay();
        $endDate = Carbon::parse($exceptionDate)->addDays(30);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            foreach ($this->candidateStartTimes($duration) as $candidateStart) {
                $candidateEnd = Carbon::parse($candidateStart)->addMinutes($duration)->format('H:i:s');

                if ($this->hasConflictAtSlot(
                    date: $date,
                    teacherId: $teacherId,
                    studentId: $studentId,
                    classroomId: $classroomId,
                    startTime: $candidateStart,
                    endTime: $candidateEnd
                )) {
                    continue;
                }

                $value = $date->toDateString().'|'.$candidateStart.'|'.$candidateEnd;
                $label = $date->format('d/m/Y').' - '.$candidateStart.' đến '.$candidateEnd;

                $suggestions[$value] = $label;

                if (count($suggestions) >= 3) {
                    return $suggestions;
                }
            }
        }

        return $suggestions;
    }

    protected function candidateStartTimes(int $durationMinutes): array
    {
        $slots = [];
        $opening = Carbon::createFromTimeString('07:00:00');
        $lastStart = Carbon::createFromTimeString('18:00:00')->subMinutes($durationMinutes);

        for ($time = $opening->copy(); $time->lte($lastStart); $time->addMinutes(60)) {
            $slots[] = $time->format('H:i:s');
        }

        return $slots;
    }

    protected function hasConflictAtSlot(
        Carbon $date,
        int $teacherId,
        int $studentId,
        ?int $classroomId,
        string $startTime,
        string $endTime
    ): bool {
        $dateStr = $date->toDateString();
        $dayOfWeek = $date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1);

        return Schedule::query()
            ->active()
            ->whereDate('start_date', '<=', $dateStr)
            ->where(function ($query) use ($dateStr) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $dateStr);
            })
            ->where(function ($query) use ($dateStr, $dayOfWeek) {
                $query->where(function ($q) use ($dateStr) {
                    $q->whereNull('day_of_week')
                        ->whereDate('start_date', $dateStr);
                })->orWhere(function ($q) use ($dayOfWeek) {
                    $q->whereNotNull('day_of_week')
                        ->whereJsonContains('day_of_week', $dayOfWeek);
                });
            })
            ->where(function ($query) use ($teacherId, $studentId, $classroomId) {
                $query->where('employee_id', $teacherId)
                    ->orWhere('student_id', $studentId);

                if ($classroomId) {
                    $query->orWhere('classroom_id', $classroomId);
                }
            })
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }
}
