<?php

namespace Quochao56\Scheduler\Filament\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Enums\ScheduleExceptionAction;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public ?int $employeeId = null;

    public ?int $studentId = null;

    public ?int $classroomId = null;

    public ?string $guardianKeyword = null;

    public function config(): array
    {
        return [
            'initialView' => 'timeGridWeek',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'allDaySlot' => false,
            'firstDay' => 1, // Thứ Hai
            'locale' => 'vi',
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '22:00:00',
        ];
    }

    protected function headerActions(): array
    {
        return [];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $startDate = Carbon::parse($fetchInfo['start']);
        $endDate = Carbon::parse($fetchInfo['end']);

        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

        // Phân quyền: Giáo viên chỉ thấy lịch của mình nếu không có quyền xem tất cả
        $user = Auth::user();
        $canViewAll = $user && ($user->isSuperAdmin() || $user->hasPermissionTo('schedules.view_all'));

        if (! $canViewAll) {
            $employee = $user?->employee ?? ($user ? Employee::where('email', $user->email)->first() : null);
            if ($employee) {
                $this->employeeId = $employee->id;
            } else {
                return [];
            }
        }

        $schedulesQuery = Schedule::active()
            ->where('start_date', '<=', $endDateStr)
            ->where(function ($q) use ($startDateStr) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDateStr);
            });

        if ($this->employeeId) {
            $employeeId = (int) $this->employeeId;
            $schedulesQuery->where(function ($q) use ($employeeId, $startDateStr, $endDateStr) {
                $q->where('employee_id', $employeeId)
                    ->orWhereHas('exceptions', function ($sub) use ($employeeId, $startDateStr, $endDateStr) {
                        $sub->where('action', 'substitute')
                            ->where('new_employee_id', $employeeId)
                            ->whereBetween('exception_date', [$startDateStr, $endDateStr]);
                    });
            });
        }
        if ($this->studentId) {
            $schedulesQuery->where('student_id', $this->studentId);
        }
        if ($this->classroomId) {
            $schedulesQuery->where('classroom_id', $this->classroomId);
        }
        if ($this->guardianKeyword) {
            $keyword = trim($this->guardianKeyword);
            $schedulesQuery->whereHas('student', function ($query) use ($keyword) {
                $query->where('father_name', 'like', "%{$keyword}%")
                    ->orWhere('mother_name', 'like', "%{$keyword}%")
                    ->orWhere('father_phone', 'like', "%{$keyword}%")
                    ->orWhere('mother_phone', 'like', "%{$keyword}%");
            });
        }

        $schedules = $schedulesQuery->with(['student', 'employee', 'classroom'])->get();

        $exceptionsQuery = ScheduleException::query()
            ->whereBetween('exception_date', [$startDateStr, $endDateStr]);

        $exceptions = $exceptionsQuery->with(['schedule.student', 'schedule.employee', 'schedule.classroom', 'newClassroom'])->get();

        $exceptionsGrouped = [];
        $rescheduledOriginalDates = [];
        foreach ($exceptions as $exception) {
            $effectiveDate = $exception->exception_date?->format('Y-m-d');

            if ($exception->action === 'reschedule' && $exception->new_exception_date) {
                $effectiveDate = $exception->new_exception_date->format('Y-m-d');

                if ($exception->exception_date?->format('Y-m-d') !== $effectiveDate) {
                    $rescheduledOriginalDates[$exception->schedule_id][$exception->exception_date->format('Y-m-d')] = true;
                }
            }

            if ($effectiveDate) {
                $exceptionsGrouped[$exception->schedule_id][$effectiveDate] = $exception;
            }
        }

        $events = [];
        $tempDate = $startDate->copy();

        while ($tempDate->lte($endDate)) {
            $dateStr = $tempDate->format('Y-m-d');
            $dayOfWeekNum = $tempDate->dayOfWeek;
            $myDayOfWeek = $dayOfWeekNum === 0 ? 1 : ($dayOfWeekNum + 1);

            foreach ($schedules as $schedule) {
                if ($schedule->start_date->format('Y-m-d') > $dateStr) {
                    continue;
                }
                if ($schedule->end_date && $schedule->end_date->format('Y-m-d') < $dateStr) {
                    continue;
                }

                $days = $schedule->day_of_week;
                if (is_string($days)) {
                    $days = json_decode($days, true);
                }

                if (! empty($days)) {
                    if (! in_array($myDayOfWeek, (array) $days)) {
                        continue;
                    }
                } else {
                    if ($schedule->start_date->format('Y-m-d') !== $dateStr) {
                        continue;
                    }
                }

                $exception = $exceptionsGrouped[$schedule->id][$dateStr] ?? null;

                if (($rescheduledOriginalDates[$schedule->id][$dateStr] ?? false) === true) {
                    continue;
                }

                if ($this->employeeId) {
                    $teachingEmployeeId = (int) $schedule->employee_id;
                    if ($exception && $exception->action === ScheduleExceptionAction::Substitute && $exception->new_employee_id) {
                        $teachingEmployeeId = (int) $exception->new_employee_id;
                    }

                    if ((int) $this->employeeId !== $teachingEmployeeId) {
                        continue;
                    }
                }

                $startTime = $schedule->start_time;
                $endTime = $schedule->end_time;
                $roomName = $schedule->classroom?->name ?? 'Không có phòng';
                $status = 'normal';
                $actionLabel = '';
                $teacherName = $schedule->employee->name;

                if ($exception) {
                    if ($exception->action === ScheduleExceptionAction::Cancel) {
                        $status = 'canceled';
                        $actionLabel = ' (Hủy)';
                    } elseif ($exception->action === ScheduleExceptionAction::Substitute) {
                        $status = 'substituted';
                        $subTeacher = $exception->new_employee_id ? Employee::find($exception->new_employee_id) : null;
                        $teacherName = $subTeacher?->name ?? 'Chưa gán';
                        if ($exception->new_classroom_id) {
                            $newRoom = $exception->newClassroom ?? Classroom::find($exception->new_classroom_id);
                            $roomName = $newRoom?->name ?? $roomName;
                        }
                        $actionLabel = ' (Dạy thay)';
                    } elseif ($exception->action === ScheduleExceptionAction::ChangeRoom) {
                        $status = 'changed_room';
                        if ($exception->new_classroom_id) {
                            $newRoom = $exception->newClassroom ?? Classroom::find($exception->new_classroom_id);
                            $roomName = $newRoom?->name ?? $roomName;
                        }
                        $actionLabel = ' (Đổi phòng)';
                    } elseif ($exception->action === ScheduleExceptionAction::Reschedule) {
                        $status = 'rescheduled';
                        $actionLabel = ' (Dời lịch)';
                        if ($exception->new_start_time) {
                            $startTime = $exception->new_start_time;
                            $endTime = $exception->new_end_time;
                        }
                        if ($exception->new_classroom_id) {
                            $newRoom = $exception->newClassroom ?? Classroom::find($exception->new_classroom_id);
                            $roomName = $newRoom?->name ?? $roomName;
                        }
                    }
                }

                $statusClass = 'event-status-normal';
                if ($status === 'canceled') {
                    $statusClass = 'event-status-canceled';
                } elseif ($status === 'substituted') {
                    $statusClass = 'event-status-substituted';
                } elseif ($status === 'rescheduled') {
                    $statusClass = 'event-status-rescheduled';
                } elseif ($status === 'changed_room') {
                    $statusClass = 'event-status-substituted';
                } elseif ($schedule->type === 'group') {
                    $statusClass = 'event-status-group';
                }

                $studentName = $schedule->student?->name ?? 'Dạy nhóm';
                $typePrefix = $schedule->type === 'group' ? '[Nhóm]' : '[1-1]';

                $statusText = 'Bình thường';
                if ($status === 'canceled') {
                    $statusText = 'Đã hủy';
                } elseif ($status === 'substituted') {
                    $statusText = 'Dạy thay';
                } elseif ($status === 'rescheduled') {
                    $statusText = 'Dời lịch';
                } elseif ($status === 'changed_room') {
                    $statusText = 'Đổi phòng học';
                }

                $titleText = "{$typePrefix} ".($schedule->type === 'group' ? 'Dạy nhóm' : $studentName)."\n".
                             "GV: {$teacherName}\n".
                             "Phòng: {$roomName}\n".
                             "Trạng thái: {$statusText}";

                $canEdit = ScheduleResource::canEdit($schedule);
                $url = $canEdit ? ScheduleResource::getUrl('edit', ['record' => $schedule->id]) : ScheduleResource::getUrl('view', ['record' => $schedule->id]);
                $events[] = [
                    'id' => $schedule->id.'-'.$dateStr,
                    'title' => $titleText,
                    'start' => $dateStr.'T'.$startTime,
                    'end' => $dateStr.'T'.$endTime,
                    'url' => $url,
                    'classNames' => [
                        $schedule->type === 'group' ? 'event-group' : 'event-individual',
                        $statusClass,
                    ],
                    'extendedProps' => [
                        'tooltip' => 'HS: '.$studentName.' | GV: '.$teacherName.' | Phòng: '.$roomName.' | '.$startTime.'-'.$endTime,
                    ],
                ];
            }

            foreach ($exceptions as $exc) {
                if (! $exc->new_exception_date || $exc->new_exception_date->format('Y-m-d') !== $dateStr) {
                    continue;
                }
                if ($exc->action !== 'reschedule') {
                    continue;
                }

                $sched = $exc->schedule;
                if (! $sched) {
                    continue;
                }

                if ($exc->exception_date?->format('Y-m-d') === $dateStr) {
                    continue;
                }

                $scheduleDays = $sched->day_of_week;
                if (is_string($scheduleDays)) {
                    $scheduleDays = json_decode($scheduleDays, true);
                }

                if (in_array($myDayOfWeek, (array) $scheduleDays, true)) {
                    continue;
                }

                $teacherId = $exc->new_employee_id ?? $sched->employee_id;
                $studentId = $sched->student_id;
                $roomId = $exc->new_classroom_id ?? $sched->classroom_id;

                if ($this->employeeId && $this->employeeId != $teacherId) {
                    continue;
                }
                if ($this->studentId && $this->studentId != $studentId) {
                    continue;
                }
                if ($this->classroomId && $this->classroomId != $roomId) {
                    continue;
                }

                $roomName = $exc->newClassroom?->name ?? ($sched->classroom?->name ?? 'Không có phòng');
                $teacherName = Employee::find($teacherId)?->name ?? $sched->employee->name;

                $excStudentName = $sched->student?->name ?? 'Dạy nhóm';
                $excTypePrefix = $sched->type === 'group' ? '[Nhóm]' : '[1-1]';
                $excTitleText = "{$excTypePrefix} ".($sched->type === 'group' ? 'Dạy nhóm' : $excStudentName)."\n".
                                "GV: {$teacherName}\n".
                                "Phòng: {$roomName}\n".
                                'Trạng thái: Dời sang';

                $canEditSched = ScheduleResource::canEdit($sched);
                $url = $canEditSched ? ScheduleResource::getUrl('edit', ['record' => $sched->id]) : ScheduleResource::getUrl('view', ['record' => $sched->id]);
                $events[] = [
                    'id' => $sched->id.'-'.$dateStr.'-rescheduled',
                    'title' => $excTitleText,
                    'start' => $dateStr.'T'.($exc->new_start_time ?? $sched->start_time),
                    'end' => $dateStr.'T'.($exc->new_end_time ?? $sched->end_time),
                    'url' => $url,
                    'classNames' => [
                        $sched->type === 'group' ? 'event-group' : 'event-individual',
                        'event-status-rescheduled',
                    ],
                    'extendedProps' => [
                        'tooltip' => 'HS: '.$excStudentName.' | GV: '.$teacherName.' | Phòng: '.$roomName,
                    ],
                ];
            }

            $tempDate->addDay();
        }

        return $events;
    }
}
