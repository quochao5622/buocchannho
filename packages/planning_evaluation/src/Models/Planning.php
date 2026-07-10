<?php

namespace Quochao56\PlanningEvaluation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\Student\Models\Student;

class Planning extends Model implements AuditableContract
{
    use Auditable;

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
        static::saving(function (self $planning): void {
            if (is_array($planning->planning_details)) {
                $planning->planning_details = static::replaceTabsInArray($planning->planning_details);
            }
        });

        static::saved(function (self $planning): void {
            if (! $planning->wasChanged() && ! $planning->wasRecentlyCreated) {
                return;
            }

            PlanningHistory::query()->create([
                'planning_id' => $planning->getKey(),
                'snapshot' => $planning->fresh()?->attributesToArray() ?? $planning->attributesToArray(),
                'saved_by' => Auth::id(),
            ]);
        });
    }

    protected static function replaceTabsInArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = static::replaceTabsInArray($value);
            } elseif (is_string($value)) {
                $array[$key] = str_replace("\t", ' ', $value);
            }
        }

        return $array;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function evaluation()
    {
        return $this->hasOne(Evaluation::class, 'planning_id');
    }

    public function histories()
    {
        return $this->hasMany(PlanningHistory::class, 'planning_id');
    }
}
