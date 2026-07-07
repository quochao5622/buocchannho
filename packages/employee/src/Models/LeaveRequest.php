<?php

namespace Quochao56\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Core\Models\User;

class LeaveRequest extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'leave_requests';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'half_day',
        'half_day_session',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'half_day' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::deleted(function (LeaveRequest $leaveRequest) {
            EmployeeAttendance::where('employee_id', $leaveRequest->employee_id)
                ->whereBetween('date', [$leaveRequest->start_date, $leaveRequest->end_date])
                ->where('status', 'on_leave')
                ->delete();
        });
    }
}
