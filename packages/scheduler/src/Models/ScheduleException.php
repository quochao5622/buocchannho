<?php

namespace Quochao56\Scheduler\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Employee\Models\Employee;

class ScheduleException extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'schedule_exceptions';

    protected $fillable = [
        'schedule_id',
        'exception_date',
        'action',
        'new_employee_id',
        'new_classroom_id',
        'new_start_time',
        'new_end_time',
        'reason',
        'notes',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'action' => 'string',
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
}
