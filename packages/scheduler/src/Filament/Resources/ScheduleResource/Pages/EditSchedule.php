<?php

namespace Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource;
use Quochao56\Student\Models\Student;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['type'] ?? null) !== 'individual') {
            return $data;
        }

        $student = Student::query()
            ->with('currentAssignment:id,student_id,employee_id')
            ->find($data['student_id'] ?? null);

        $assignedTeacherId = $student?->currentAssignment?->employee_id;

        if (! $assignedTeacherId) {
            throw ValidationException::withMessages([
                'student_id' => trans('packages.scheduler::scheduler.schedules.validation.student_not_assigned_warning'),
            ]);
        }

        $data['employee_id'] = (int) $assignedTeacherId;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
