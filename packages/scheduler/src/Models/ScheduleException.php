<?php

namespace Quochao56\Scheduler\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Enums\ScheduleExceptionAction;
use Quochao56\Scheduler\Enums\ScheduleExceptionStatus;

class ScheduleException extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'schedule_exceptions';

    protected $fillable = [
        'schedule_id',
        'exception_date',
        'new_exception_date',
        'action',
        'cancel_actor',
        'new_employee_id',
        'new_classroom_id',
        'new_start_time',
        'new_end_time',
        'reason',
        'notes',
        'status',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'new_exception_date' => 'date',
        'action' => ScheduleExceptionAction::class,
        'cancel_actor' => 'string',
        'status' => ScheduleExceptionStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function newEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'new_employee_id');
    }

    public function newClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'new_classroom_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', ScheduleExceptionStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ScheduleExceptionStatus::Approved);
    }

    public function isPending(): bool
    {
        return $this->status === ScheduleExceptionStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === ScheduleExceptionStatus::Approved;
    }
}
