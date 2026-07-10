<?php

namespace Quochao56\Scheduler\Enums;

enum ScheduleExceptionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => trans('packages.scheduler::scheduler.exceptions.status.pending'),
            self::Approved => trans('packages.scheduler::scheduler.exceptions.status.approved'),
            self::Rejected => trans('packages.scheduler::scheduler.exceptions.status.rejected'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
