<?php

namespace Quochao56\Scheduler\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Quochao56\Core\Enum\BaseStatusEnum;

class Classroom extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $table = 'classrooms';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'classroom_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', BaseStatusEnum::Active);
    }
}
