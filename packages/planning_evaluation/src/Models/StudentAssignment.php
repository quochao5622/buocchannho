<?php

namespace Quochao56\PlanningEvaluation\Models;

use Quochao56\Student\Models\Student;
use Quochao56\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class StudentAssignment extends Model
{
    protected $table = 'student_assignments';

    protected $fillable = [
        'student_id',
        'employee_id',
        'assigned_at',
        'unassigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
