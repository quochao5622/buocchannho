<?php

namespace Quochao56\Student\Models;

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
        return $this->hasMany(\Quochao56\PlanningEvaluation\Models\Planning::class, 'student_id');
    }

    public function assignments()
    {
        return $this->hasMany(\Quochao56\PlanningEvaluation\Models\StudentAssignment::class, 'student_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(\Quochao56\PlanningEvaluation\Models\StudentAssignment::class, 'student_id')
            ->whereNull('unassigned_at');
    }

    public function currentTeacher()
    {
        return $this->hasOneThrough(
            \Quochao56\Employee\Models\Employee::class,
            \Quochao56\PlanningEvaluation\Models\StudentAssignment::class,
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
