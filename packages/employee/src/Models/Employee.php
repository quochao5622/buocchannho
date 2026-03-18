<?php

namespace Quochao56\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\BaseStatusEnum;

class Employee extends Model
{
    use HasFactory;

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
        return $this->hasMany(\Quochao56\PlanningEvaluation\Models\Planning::class, 'employee_id');
    }

    public function setNameAttribute($value)
    {
        // strip tags and trim whitespace
        $this->attributes['name'] = trim(strip_tags($value));
    }
}
