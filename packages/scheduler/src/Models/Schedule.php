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

class Schedule extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'schedules';

    protected $fillable = [
        'student_id',
        'employee_id',
        'classroom_id',
        'title',
        'type',
        'day_of_week',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'day_of_week' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ScheduleException::class, 'schedule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'schedule_id');
    }
}
