<?php

namespace Quochao56\SessionLog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\BehaviorIntensityEnum;
use Quochao56\Student\Models\Student;

class BehaviorIncident extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'behavior_incidents';

    protected $fillable = [
        'student_id',
        'employee_id',
        'incident_date',
        'antecedent',
        'behavior',
        'consequence',
        'duration_minutes',
        'intensity',
        'notes',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'intensity' => BehaviorIntensityEnum::class,
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
