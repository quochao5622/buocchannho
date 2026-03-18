<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages;

use App\Enum\BaseStatusEnum;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Quochao56\PlanningEvaluation\Filament\Actions\ExportEvaluationWordAction;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;

class EditEvaluation extends EditRecord
{
    protected static string $resource = EvaluationResource::class;

    public function getBreadcrumbs(): array
    {
        $planning = $this->getRecord()?->planning;

        return [
            PlanningResource::getUrl('index') => PlanningResource::getPluralModelLabel(),
            PlanningResource::getUrl('edit', ['record' => $planning]) => $planning->name,
            EvaluationResource::getUrl('index', ['planning' => $planning]) => EvaluationResource::getPluralModelLabel(),
            trans('filament-panels::resources/pages/edit-record.breadcrumb'),
        ];
    }


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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->ensurePublishedHasAllAssessments($data);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportEvaluationWordAction::make(),
            $this->getSaveFormAction()
                ->submit(null)
                ->action(fn() => $this->save())
                ->keyBindings(['mod+s']),
                DeleteAction::make(),
        ];
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
