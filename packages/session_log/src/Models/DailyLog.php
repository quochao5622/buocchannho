<?php

namespace Quochao56\SessionLog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\DailyLogEmotionEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogRatingEnum;
use Quochao56\SessionLog\Filament\Enums\DailyLogStatusEnum;
use Quochao56\Student\Models\Student;

class DailyLog extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'daily_logs';

    protected $fillable = [
        'student_id',
        'employee_id',
        'log_date',
        'emotion',
        'focus_level',
        'cooperation_level',
        'eating_note',
        'sleeping_note',
        'hygiene_note',
        'general_note',
        'status',
    ];

    protected $casts = [
        'log_date' => 'date',
        'emotion' => DailyLogEmotionEnum::class,
        'focus_level' => DailyLogRatingEnum::class,
        'cooperation_level' => DailyLogRatingEnum::class,
        'status' => DailyLogStatusEnum::class,
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
