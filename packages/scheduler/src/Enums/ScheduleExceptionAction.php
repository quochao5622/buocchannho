<?php

namespace Quochao56\Scheduler\Enums;

enum ScheduleExceptionAction: string
{
    case Cancel = 'cancel';
    case Reschedule = 'reschedule';
    case Substitute = 'substitute';
    case ChangeRoom = 'change_room';

    public function label(): string
    {
        return match ($this) {
            self::Cancel => trans('packages.scheduler::scheduler.exceptions.action.cancel'),
            self::Reschedule => trans('packages.scheduler::scheduler.exceptions.action.reschedule'),
            self::Substitute => trans('packages.scheduler::scheduler.exceptions.action.substitute'),
            self::ChangeRoom => trans('packages.scheduler::scheduler.exceptions.action.change_room'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Cancel => 'danger',
            self::Reschedule => 'warning',
            self::Substitute => 'info',
            self::ChangeRoom => 'success',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Cancel => 'Hủy buổi',
            self::Reschedule => 'Dời lịch',
            self::Substitute => 'Dạy thay',
            self::ChangeRoom => 'Đổi phòng',
        };
    }
}
