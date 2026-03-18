<?php
namespace Quochao56\PlanningEvaluation\Models;

use App\Enum\BaseStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    protected $fillable = [
        'name',
        'description',
        'employee_id',
        'student_id',
        'start_date',
        'end_date',
        'planning_details',
        'status',
    ];

    protected $casts = [
        'planning_details' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => BaseStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::saved(function (self $planning): void {
            PlanningHistory::query()->create([
                'planning_id' => $planning->getKey(),
                'snapshot' => $planning->fresh()?->attributesToArray() ?? $planning->attributesToArray(),
                'saved_by' => Auth::id(),
            ]);
        });
    }

    public function employee()
    {
        return $this->belongsTo(\Quochao56\Employee\Models\Employee::class, 'employee_id');
    }
    public function student()
    {
        return $this->belongsTo(\Quochao56\Student\Models\Student::class, 'student_id');
    }

    public function evaluation()
    {
        return $this->hasOne(\Quochao56\PlanningEvaluation\Models\Evaluation::class, 'planning_id');
    }

    public function histories()
    {
        return $this->hasMany(PlanningHistory::class, 'planning_id');
    }
}