<?php

namespace Quochao56\Employee\Models;

use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\Student\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\BaseStatusEnum;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Employee extends Model implements AuditableContract
{
    use HasFactory, Auditable;

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
}
