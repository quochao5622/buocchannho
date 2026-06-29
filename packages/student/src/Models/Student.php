<?php

namespace Quochao56\Student\Models;

use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\PlanningEvaluation\Models\StudentAssignment;
use Quochao56\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'student_code',
        'name',
        'nickname',
        'gender',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'dob',
        'avatar',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function plannings()
    {
        return $this->hasMany(Planning::class, 'student_id');
    }

    public function assignments()
    {
        return $this->hasMany(StudentAssignment::class, 'student_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(StudentAssignment::class, 'student_id')
            ->whereNull('unassigned_at');
    }

    public function currentTeacher()
    {
        return $this->hasOneThrough(
            Employee::class,
            StudentAssignment::class,
            'student_id',
            'id',
            'id',
            'employee_id'
        )->whereNull('student_assignments.unassigned_at');
    }

    public function setNameAttribute($value)
    {
        // strip tags and trim whitespace
        $this->attributes['name'] = trim(strip_tags($value));
    }
}
