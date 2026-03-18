<?php

namespace Quochao56\PlanningEvaluation\Models;

use Illuminate\Database\Eloquent\Model;

class PlanningHistory extends Model
{
    protected $fillable = [
        'planning_id',
        'snapshot',
        'saved_by',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function planning()
    {
        return $this->belongsTo(Planning::class, 'planning_id');
    }
}
