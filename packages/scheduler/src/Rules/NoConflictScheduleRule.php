<?php

namespace Quochao56\Scheduler\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;

class NoConflictScheduleRule implements ValidationRule
{
    public function __construct(
        protected ?int $ignoreId = null,
        protected ?int $studentId = null,
        protected ?int $employeeId = null,
        protected ?int $classroomId = null,
        protected mixed $dayOfWeek = null,
        protected ?string $startTime = null,
        protected ?string $endTime = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->studentId || ! $this->employeeId || ! $this->startTime || ! $this->endTime || ! $this->startDate) {
            return;
        }

        // 1. Check Teacher Conflict
        $teacherConflict = Schedule::active()
            ->where('employee_id', $this->employeeId)
            ->when($this->ignoreId, fn ($q) => $q->where('id', '!=', $this->ignoreId))
            ->where(function ($query) {
                $query->whereNull('day_of_week');
                if (is_array($this->dayOfWeek)) {
                    $query->orWhere(function ($q) {
                        foreach ($this->dayOfWeek as $day) {
                            $q->orWhereJsonContains('day_of_week', (int) $day);
                        }
                    });
                } elseif ($this->dayOfWeek !== null) {
                    $query->orWhereJsonContains('day_of_week', (int) $this->dayOfWeek);
                }
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $this->startDate);
                });
                if ($this->endDate) {
                    $query->where('start_date', '<=', $this->endDate);
                }
            })
            ->where(function ($query) {
                $query->where('start_time', '<', $this->endTime)
                    ->where('end_time', '>', $this->startTime);
            })
            ->first();

        if ($teacherConflict) {
            $fail("Giáo viên này đã có lịch dạy khác trùng khung giờ (Lịch: {$teacherConflict->title}).");

            return;
        }

        // 2. Check Student Conflict
        $studentConflict = Schedule::active()
            ->where('student_id', $this->studentId)
            ->when($this->ignoreId, fn ($q) => $q->where('id', '!=', $this->ignoreId))
            ->where(function ($query) {
                $query->whereNull('day_of_week');
                if (is_array($this->dayOfWeek)) {
                    $query->orWhere(function ($q) {
                        foreach ($this->dayOfWeek as $day) {
                            $q->orWhereJsonContains('day_of_week', (int) $day);
                        }
                    });
                } elseif ($this->dayOfWeek !== null) {
                    $query->orWhereJsonContains('day_of_week', (int) $this->dayOfWeek);
                }
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', $this->startDate);
                });
                if ($this->endDate) {
                    $query->where('start_date', '<=', $this->endDate);
                }
            })
            ->where(function ($query) {
                $query->where('start_time', '<', $this->endTime)
                    ->where('end_time', '>', $this->startTime);
            })
            ->first();

        if ($studentConflict) {
            $fail("Học sinh này đã có lịch học khác trùng khung giờ (Lịch: {$studentConflict->title}).");

            return;
        }

        // 3. Check Room Conflict (if classroom set)
        if ($this->classroomId) {
            $roomConflict = Schedule::active()
                ->where('classroom_id', $this->classroomId)
                ->when($this->ignoreId, fn ($q) => $q->where('id', '!=', $this->ignoreId))
                ->where(function ($query) {
                    $query->whereNull('day_of_week');
                    if (is_array($this->dayOfWeek)) {
                        $query->orWhere(function ($q) {
                            foreach ($this->dayOfWeek as $day) {
                                $q->orWhereJsonContains('day_of_week', (int) $day);
                            }
                        });
                    } elseif ($this->dayOfWeek !== null) {
                        $query->orWhereJsonContains('day_of_week', (int) $this->dayOfWeek);
                    }
                })
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $this->startDate);
                    });
                    if ($this->endDate) {
                        $query->where('start_date', '<=', $this->endDate);
                    }
                })
                ->where(function ($query) {
                    $query->where('start_time', '<', $this->endTime)
                        ->where('end_time', '>', $this->startTime);
                })
                ->first();

            if ($roomConflict) {
                $fail("Phòng học này đã được đăng ký sử dụng trong khung giờ này (Lịch: {$roomConflict->title}).");

                return;
            }
        }
    }

    /**
     * Check if a teacher is available on a specific date and time slot
     */
    public static function isTeacherAvailableOnDate(int $teacherId, string $date, string $startTime, string $endTime, ?int $ignoreScheduleId = null): bool
    {
        $dayOfWeekNum = date('N', strtotime($date));
        $myDayOfWeek = $dayOfWeekNum === 7 ? 1 : ($dayOfWeekNum + 1);

        // A. Check if the teacher has any active master schedule that is busy
        $busySchedules = Schedule::active()
            ->where('employee_id', $teacherId)
            ->when($ignoreScheduleId, fn ($q) => $q->where('id', '!=', $ignoreScheduleId))
            ->where(function ($query) use ($myDayOfWeek) {
                $query->whereJsonContains('day_of_week', $myDayOfWeek)
                    ->orWhereNull('day_of_week');
            })
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->get();

        foreach ($busySchedules as $schedule) {
            // Check if there is an exception for this schedule on this date that frees the teacher
            $exception = ScheduleException::where('schedule_id', $schedule->id)
                ->whereDate('exception_date', $date)
                ->first();

            if ($exception) {
                if (in_array($exception->action, ['cancel', 'reschedule', 'substitute'])) {
                    continue; // The teacher is free from this schedule on this day
                }
            }

            return false;
        }

        // B. Check if the teacher is busy due to being a substitute or having a rescheduled slot on this day
        $exceptionBusy = ScheduleException::whereDate('exception_date', $date)
            ->where(function ($query) use ($teacherId) {
                $query->where('action', 'substitute')
                    ->where('new_employee_id', $teacherId);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->whereNotNull('new_start_time')
                        ->where('new_start_time', '<', $endTime)
                        ->where('new_end_time', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->whereNull('new_start_time')
                        ->whereHas('schedule', function ($sQuery) use ($startTime, $endTime) {
                            $sQuery->where('start_time', '<', $endTime)
                                ->where('end_time', '>', $startTime);
                        });
                });
            })
            ->exists();

        if ($exceptionBusy) {
            return false;
        }

        return true;
    }
}
