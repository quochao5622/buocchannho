<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages;

use App\Enum\BaseStatusEnum;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;

class CreateEvaluation extends CreateRecord
{
    protected static string $resource = EvaluationResource::class;

    public function getRules(): array
    {
        return [
            ...parent::getRules(),
            'data.evaluation_details.*.muc_tieu.*.danh_gia' => ['nullable', 'required_if:data.status,' . BaseStatusEnum::Published->value],
        ];
    }

    protected function getValidationMessages(): array
    {
        return [
            'data.evaluation_details.*.muc_tieu.*.danh_gia.required_if' => trans('planning-evaluation::evaluation.validation.danh_gia_required_when_published'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->ensurePublishedHasAllAssessments($data);

        return $data;
    }

    protected function ensurePublishedHasAllAssessments(array $data): void
    {
        $status = data_get($data, 'status');

        if ($status !== BaseStatusEnum::Published->value) {
            return;
        }

        $messages = [];

        foreach ((array) data_get($data, 'evaluation_details', []) as $rowIndex => $row) {
            foreach ((array) data_get($row, 'muc_tieu', []) as $goalIndex => $goal) {
                if (blank(data_get($goal, 'danh_gia'))) {
                    $path = "evaluation_details.{$rowIndex}.muc_tieu.{$goalIndex}.danh_gia";
                    $message = trans('planning-evaluation::evaluation.validation.danh_gia_required_when_published');
                    $messages[$path] = $message;
                    $messages["data.{$path}"] = $message;
                }
            }
        }

        if (! empty($messages)) {
            throw ValidationException::withMessages($messages);
        }
    }
}
