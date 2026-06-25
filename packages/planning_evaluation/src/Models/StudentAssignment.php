<?php

namespace Quochao56\PlanningEvaluation\Models;

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
        return $this->belongsTo(\Quochao56\Student\Models\Student::class, 'student_id');
    }

    public function employee()
    {
        return $this->belongsTo(\Quochao56\Employee\Models\Employee::class, 'employee_id');
    }
}
