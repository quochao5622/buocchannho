<?php

namespace Quochao56\Student\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Core\Models\User;

class StudentLeaveRequest extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'student_leave_requests';

    protected $fillable = [
        'student_id',
        'start_date',
        'end_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (StudentLeaveRequest $model) {
            if (Auth::check() && ! $model->created_by) {
                $model->created_by = Auth::id();
            }
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
