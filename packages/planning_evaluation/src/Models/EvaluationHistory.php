<?php

namespace Quochao56\PlanningEvaluation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EvaluationHistory extends Model
{
    protected $fillable = [
        'evaluation_id',
        'snapshot',
        'saved_by',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'saved_by');
    }
}
