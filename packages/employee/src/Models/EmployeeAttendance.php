<?php

namespace Quochao56\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Scheduler\Models\Schedule;

class EmployeeAttendance extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'employee_attendances';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in_at',
        'check_out_at',
        'status',
        'check_in_ip',
        'check_out_ip',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'total_hours',
        'notes',
        // Verification & geofencing
        'flagged_location',
        'verification_status',
        'unassigned_schedule',
        'schedule_id',
        // Open session / auto-close
        'auto_closed',
        'flagged_missing_checkin',
        // Correction tracking
        'corrected_by',
        'corrected_at',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'corrected_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'flagged_location' => 'boolean',
        'unassigned_schedule' => 'boolean',
        'auto_closed' => 'boolean',
        'flagged_missing_checkin' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeOpenSession($query)
    {
        return $query->whereNull('check_out_at');
    }
}
