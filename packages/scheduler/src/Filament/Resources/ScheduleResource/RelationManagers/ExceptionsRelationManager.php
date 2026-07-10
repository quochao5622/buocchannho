<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\RelationManagers;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Core\Models\User;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Enums\ScheduleExceptionAction;
use Quochao56\Scheduler\Enums\ScheduleExceptionStatus;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;

class ExceptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'exceptions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('packages.scheduler::scheduler.exceptions.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.scheduler::scheduler.exceptions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.scheduler::scheduler.exceptions.plural_model_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

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

            DatePicker::make('new_exception_date')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_exception_date'))
                ->native(false)
                ->displayFormat('d/m/Y')
                ->visible(fn (Get $get) => $get('action') === 'reschedule')
                ->required(fn (Get $get) => $get('action') === 'reschedule')
                ->default(fn (Get $get) => $get('exception_date')),

            Select::make('action')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.action'))
                ->options([
                    'cancel' => trans('packages.scheduler::scheduler.exceptions.action.cancel'),
                    'reschedule' => trans('packages.scheduler::scheduler.exceptions.action.reschedule'),
                    'substitute' => trans('packages.scheduler::scheduler.exceptions.action.substitute'),
                    'change_room' => trans('packages.scheduler::scheduler.exceptions.action.change_room'),
                ])
                ->default('cancel')
                ->live()
                ->required(),

            Select::make('cancel_actor')
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.cancel_actor'))
                ->options([
                    'teacher' => trans('packages.scheduler::scheduler.exceptions.cancel_actor.teacher'),
                    'student' => trans('packages.scheduler::scheduler.exceptions.cancel_actor.student'),
                ])
                ->visible(fn (Get $get) => $get('action') === 'cancel')
                ->required(fn (Get $get) => $get('action') === 'cancel'),

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
                ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_classroom_id'))
                ->options(Classroom::active()->pluck('name', 'id'))
                ->searchable()
                ->visible(fn (Get $get) => in_array($get('action'), ['reschedule', 'substitute', 'change_room']))
                ->required(fn (Get $get) => $get('action') === 'change_room')
                ->nullable(fn (Get $get) => $get('action') !== 'change_room'),

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
                    $suggestion = trans('packages.scheduler::scheduler.exceptions.messages.make_up_suggestion_prefix', [
                        'date' => Carbon::parse($date)->format('d/m/Y'),
                        'start' => $start,
                        'end' => $end,
                    ]);
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
                    ->color(fn (ScheduleExceptionAction $state): string => $state->color())
                    ->formatStateUsing(fn (ScheduleExceptionAction $state): string => $state->label()),

                BadgeColumn::make('status')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.status'))
                    ->color(fn (ScheduleExceptionStatus $state): string => $state->color())
                    ->formatStateUsing(fn (ScheduleExceptionStatus $state): string => $state->label()),

                TextColumn::make('cancel_actor')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.cancel_actor'))
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? trans("packages.scheduler::scheduler.exceptions.cancel_actor.{$state}")
                        : '-')
                    ->placeholder('-'),

                TextColumn::make('new_exception_date')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_exception_date'))
                    ->date('d/m/Y')
                    ->placeholder('-'),

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
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.new_classroom_id'))
                    ->placeholder('-'),

                TextColumn::make('requestedBy.name')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.requested_by'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reviewedBy.name')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.reviewed_by'))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('review_note')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.review_note'))
                    ->limit(40)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reason')
                    ->label(trans('packages.scheduler::scheduler.exceptions.fields.reason'))
                    ->limit(50),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(trans('packages.scheduler::scheduler.exceptions.actions.create'))
                    ->modalHeading(trans('packages.scheduler::scheduler.exceptions.actions.create_heading'))
                    ->mutateDataUsing(function (array $data): array {
                        $canApprove = auth()->user()?->can('approve', ScheduleException::class);

                        // Admin tạo trực tiếp → approved ngay; giáo viên tạo → pending
                        $data['status'] = $canApprove ? ScheduleExceptionStatus::Approved->value : ScheduleExceptionStatus::Pending->value;
                        $data['requested_by'] = auth()->id();

                        if ($canApprove) {
                            $data['reviewed_by'] = auth()->id();
                            $data['reviewed_at'] = now();
                        }

                        return $data;
                    })
                    ->after(function (ScheduleException $record, $livewire): void {
                        if ($record->isPending()) {
                            // Thông báo cho user biết đang chờ duyệt
                            Notification::make()
                                ->warning()
                                ->title(trans('packages.scheduler::scheduler.exceptions.messages.pending_info'))
                                ->send();
                            $livewire->dispatch('notificationsSent');

                            // Gửi thông báo tới admin có quyền duyệt
                            $this->notifyApprovers($record);
                        } else {
                            $this->afterExceptionSaved($record);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(trans('packages.scheduler::scheduler.exceptions.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(trans('packages.scheduler::scheduler.exceptions.actions.approve_confirm'))
                    ->visible(fn (ScheduleException $record) => $record->isPending() && auth()->user()?->can('approve', ScheduleException::class))
                    ->action(function (ScheduleException $record, $livewire): void {
                        $record->update([
                            'status' => ScheduleExceptionStatus::Approved->value,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title(trans('packages.scheduler::scheduler.exceptions.messages.approved_success'))
                            ->send();
                        $livewire->dispatch('notificationsSent');

                        $this->afterExceptionSaved($record->fresh());
                    }),

                Action::make('reject')
                    ->label(trans('packages.scheduler::scheduler.exceptions.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(trans('packages.scheduler::scheduler.exceptions.actions.reject_heading'))
                    ->form([
                        Textarea::make('review_note')
                            ->label(trans('packages.scheduler::scheduler.exceptions.fields.review_note'))
                            ->placeholder('Nhập lý do từ chối...')
                            ->rows(3)
                            ->required(),
                    ])
                    ->visible(fn (ScheduleException $record) => $record->isPending() && auth()->user()?->can('approve', ScheduleException::class))
                    ->action(function (ScheduleException $record, array $data, $livewire): void {
                        $record->update([
                            'status' => ScheduleExceptionStatus::Rejected->value,
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'review_note' => $data['review_note'],
                        ]);

                        Notification::make()
                            ->warning()
                            ->title(trans('packages.scheduler::scheduler.exceptions.messages.rejected_success'))
                            ->send();
                        $livewire->dispatch('notificationsSent');
                    }),

                EditAction::make()
                    ->label(trans('packages.scheduler::scheduler.exceptions.actions.edit'))
                    ->modalHeading(trans('packages.scheduler::scheduler.exceptions.actions.edit_heading'))
                    ->visible(fn (ScheduleException $record) => ! $record->isApproved() || auth()->user()?->can('approve', ScheduleException::class))
                    ->after(function (ScheduleException $record): void {
                        $this->afterExceptionSaved($record);
                    }),

                DeleteAction::make()
                    ->label(trans('packages.scheduler::scheduler.exceptions.actions.delete'))
                    ->visible(fn (ScheduleException $record) => ! $record->isApproved() || auth()->user()?->can('approve', ScheduleException::class)),
            ]);
    }

    protected function afterExceptionSaved(ScheduleException $exception): void
    {
        //
    }

    protected function notifyApprovers(ScheduleException $exception): void
    {
        $admins = User::permission('schedule_exceptions.approve')->get();
        $superAdmins = User::where('is_super_admin', true)->get();

        $approvers = $admins->merge($superAdmins)
            ->unique('id')
            ->reject(fn ($user) => $user->id === auth()->id());

        /** @var Schedule|null $schedule */
        $schedule = $this->getOwnerRecord();
        $requestedByName = auth()->user()?->name ?? 'Giáo viên';
        $dateStr = $exception->exception_date?->format('d/m/Y') ?? '';
        $actionLabel = $exception->action instanceof ScheduleExceptionAction
            ? $exception->action->shortLabel()
            : 'Điều chỉnh';

        $viewUrl = $schedule
            ? ScheduleResource::getUrl('view', ['record' => $schedule->id])
            : null;

        foreach ($approvers as $approver) {
            $notification = Notification::make()
                ->warning()
                ->title(trans('packages.scheduler::scheduler.exceptions.notifications.pending_title'))
                ->body("{$requestedByName} vừa tạo yêu cầu **{$actionLabel}** lịch học vào ngày {$dateStr}. Vui lòng xem xét và duyệt.")
                ->icon('heroicon-o-calendar');

            if ($viewUrl) {
                $notification->actions([
                    Action::make('view')
                        ->label('Xem & Duyệt')
                        ->url($viewUrl)
                        ->button()
                        ->color('warning')
                        ->markAsRead(),
                ]);
            }

            $notification->sendToDatabase($approver);
        }
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
                if ($date->isWeekday() && $candidateStart < settings('office_afternoon_end', '17:30')) {
                    continue;
                }

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

                $dayOfWeekName = trans('packages.scheduler::scheduler.schedules.day_of_week.'.($date->dayOfWeek === 0 ? 1 : ($date->dayOfWeek + 1)));
                $value = $date->toDateString().'|'.$candidateStart.'|'.$candidateEnd;
                $label = $dayOfWeekName.', '.$date->format('d/m/Y').' - '.$candidateStart.' đến '.$candidateEnd;

                $suggestions[$value] = $label;
            }
        }

        return $suggestions;
    }

    protected function candidateStartTimes(int $durationMinutes): array
    {
        $slots = [];
        $opening = Carbon::createFromTimeString('07:00:00');
        $lastStart = Carbon::createFromTimeString('21:00:00')->subMinutes($durationMinutes);

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
