<?php

namespace Quochao56\Scheduler\Filament\Widgets;

use Carbon\Carbon;
use Quochao56\Employee\Models\Employee;
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

    public function fetchEvents(array $fetchInfo): array
    {
        $startDate = Carbon::parse($fetchInfo['start']);
        $endDate = Carbon::parse($fetchInfo['end']);

        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

        $schedulesQuery = Schedule::active()
            ->where('start_date', '<=', $endDateStr)
            ->where(function ($q) use ($startDateStr) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDateStr);
            });

        if ($this->employeeId) {
            $schedulesQuery->where('employee_id', $this->employeeId);
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
        foreach ($exceptions as $exception) {
            $exceptionsGrouped[$exception->schedule_id][$exception->exception_date->format('Y-m-d')] = $exception;
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

                if ($schedule->day_of_week !== null) {
                    if (! in_array($myDayOfWeek, (array) $schedule->day_of_week)) {
                        continue;
                    }
                } else {
                    if ($schedule->start_date->format('Y-m-d') !== $dateStr) {
                        continue;
                    }
                }

                $exception = $exceptionsGrouped[$schedule->id][$dateStr] ?? null;

                $startTime = $schedule->start_time;
                $endTime = $schedule->end_time;
                $roomName = $schedule->classroom?->name ?? 'Không có phòng';
                $status = 'normal';
                $actionLabel = '';
                $teacherName = $schedule->employee->name;

                if ($exception) {
                    if ($exception->action === 'cancel') {
                        $status = 'canceled';
                        $actionLabel = ' (Hủy)';
                    } elseif ($exception->action === 'substitute') {
                        $status = 'substituted';
                        $subTeacher = $exception->new_employee_id ? Employee::find($exception->new_employee_id) : null;
                        $teacherName = $subTeacher?->name ?? 'Chưa gán';
                        $actionLabel = ' (Dạy thay)';
                    } elseif ($exception->action === 'reschedule') {
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

                $backgroundColor = '#10b981'; // Emerald (normal)
                $borderColor = '#059669';
                if ($status === 'canceled') {
                    $backgroundColor = '#ef4444'; // Rose
                    $borderColor = '#dc2626';
                } elseif ($status === 'substituted') {
                    $backgroundColor = '#6366f1'; // Indigo
                    $borderColor = '#4f46e5';
                } elseif ($status === 'rescheduled') {
                    $backgroundColor = '#f59e0b'; // Amber
                    $borderColor = '#d97706';
                } elseif ($schedule->type === 'group') {
                    $backgroundColor = '#3b82f6'; // Blue
                    $borderColor = '#2563eb';
                }

                $events[] = [
                    'id' => $schedule->id.'-'.$dateStr,
                    'title' => $schedule->title.$actionLabel.' - '.$schedule->student->name,
                    'start' => $dateStr.'T'.$startTime,
                    'end' => $dateStr.'T'.$endTime,
                    'url' => '/admin/schedules/'.$schedule->id.'/edit',
                    'backgroundColor' => $backgroundColor,
                    'borderColor' => $borderColor,
                    'textColor' => '#ffffff',
                    'classNames' => [$schedule->type === 'group' ? 'event-group' : 'event-individual'],
                    'extendedProps' => [
                        'tooltip' => 'HS: '.$schedule->student->name.' | GV: '.$teacherName.' | Phòng: '.$roomName.' | '.$startTime.'-'.$endTime,
                    ],
                ];
            }

            foreach ($exceptions as $exc) {
                if ($exc->exception_date->format('Y-m-d') !== $dateStr) {
                    continue;
                }
                if ($exc->action !== 'reschedule') {
                    continue;
                }

                $sched = $exc->schedule;
                if (! $sched) {
                    continue;
                }

                if (in_array($myDayOfWeek, (array) $sched->day_of_week)) {
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

                $events[] = [
                    'id' => $sched->id.'-'.$dateStr.'-rescheduled',
                    'title' => $sched->title.' (Dời sang) - '.$sched->student->name,
                    'start' => $dateStr.'T'.($exc->new_start_time ?? $sched->start_time),
                    'end' => $dateStr.'T'.($exc->new_end_time ?? $sched->end_time),
                    'url' => '/admin/schedules/'.$sched->id.'/edit',
                    'backgroundColor' => '#f59e0b', // Amber
                    'borderColor' => '#d97706',
                    'textColor' => '#ffffff',
                    'classNames' => [$sched->type === 'group' ? 'event-group' : 'event-individual'],
                    'extendedProps' => [
                        'tooltip' => 'HS: '.$sched->student->name.' | GV: '.$teacherName.' | Phòng: '.$roomName,
                    ],
                ];
            }

            $tempDate->addDay();
        }

        return $events;
    }
}
