<?php

namespace Quochao56\Scheduler\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Employee\Models\Employee;
use Quochao56\Student\Models\Student;

class Attendance extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'student_id',
        'schedule_id',
        'attendance_date',
        'status',
        'check_in_at',
        'check_out_at',
        'total_hours',
        'notes',
        'session_note',
        'absence_reason',
        'reported_by',
        'make_up_scheduled',
        'verified_by_employee_id',
        'actual_employee_id',
        // Dạy bù & dạy thay
        'make_up_for_attendance_id',
        'substitute_reason',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'total_hours' => 'decimal:2',
        'status' => 'string',
        'make_up_scheduled' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by_employee_id');
    }

    public function actualEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'actual_employee_id');
    }

    /**
     * Bản ghi điểm danh gốc mà buổi dạy bù này thay thế.
     */
    public function makeUpFor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'make_up_for_attendance_id');
    }

    /**
     * Các buổi dạy bù được tạo ra cho bản ghi điểm danh này.
     */
    public function makeUpAttendances(): HasMany
    {
        return $this->hasMany(self::class, 'make_up_for_attendance_id');
    }

    public function scopeMakeUpPending($query)
    {
        return $query->where('status', 'make_up_pending');
    }
}
