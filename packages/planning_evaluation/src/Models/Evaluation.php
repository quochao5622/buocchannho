<?php
namespace Quochao56\PlanningEvaluation\Models;

use App\Enum\BaseStatusEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'planning_id',
        'evaluation_details',
        'status',

    ];

    protected $casts = [
        'evaluation_details' => 'array',
        'status' => BaseStatusEnum::class,
    ];

    protected static function booted(): void
    {
        static::saved(function (self $evaluation): void {
            EvaluationHistory::query()->create([
                'evaluation_id' => $evaluation->getKey(),
                'snapshot' => $evaluation->fresh()?->attributesToArray() ?? $evaluation->attributesToArray(),
                'saved_by' => Auth::id(),
            ]);
        });
    }

    public function planning()
    {
        return $this->belongsTo(\Quochao56\PlanningEvaluation\Models\Planning::class, 'planning_id');
    }

    public function histories()
    {
        return $this->hasMany(EvaluationHistory::class, 'evaluation_id');
    }

    public static function upsertFromPlanning(Planning $planning): self
    {
        return static::query()->firstOrCreate(
            ['planning_id' => $planning->getKey()],
            [
                'name' => $planning->name,
                'description' => $planning->description,
                'status' => BaseStatusEnum::Draft->value,
                'evaluation_details' => static::mapPlanningDetailsToEvaluationDetails($planning->planning_details ?? []),
            ],
        );
    }

    protected static function mapPlanningDetailsToEvaluationDetails(array $planningDetails): array
    {
        return collect($planningDetails)
            ->map(function (array $row): array {
                $linhVuc = collect($row['linh_vuc'] ?? [])
                    ->pluck('content')
                    ->filter()
                    ->implode("\n");

                $mucTieu = collect($row['muc_tieu'] ?? [])
                    ->map(function (array $goal): array {
                        return [
                            'content' => (string) ($goal['content'] ?? ''),
                            'danh_gia' => null,
                            'nhan_xet' => null,
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'linh_vuc' => $linhVuc,
                    'muc_tieu' => $mucTieu,
                ];
            })
            ->values()
            ->all();
    }
}