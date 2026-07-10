<?php

namespace Quochao56\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Models\User;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\Student\Models\Student;

class Employee extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'phone',
        'address',
        'position',
        'employment_type',
        'hired_at',
        'probation_end_at',
        'status',
        'avatar',
        'dob',
        'gender',
    ];

    protected $casts = [
        'hired_at' => 'date',
        'probation_end_at' => 'date',
        'dob' => 'date',
        'status' => BaseStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function plannings()
    {
        return $this->hasMany(Planning::class, 'employee_id');
    }

    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'student_assignments',
            'employee_id',
            'student_id'
        )->whereNull('student_assignments.unassigned_at');
    }

    public function setNameAttribute($value)
    {
        // strip tags and trim whitespace
        $this->attributes['name'] = trim(strip_tags($value));
    }

    public function scopeActive($query)
    {
        return $query->where('status', BaseStatusEnum::Active);
    }
}
