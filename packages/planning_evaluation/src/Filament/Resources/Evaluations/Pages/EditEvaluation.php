<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Traits\HasAutoSave;
use Quochao56\PlanningEvaluation\Filament\Actions\ApproveAction;
use Quochao56\PlanningEvaluation\Filament\Actions\ExportEvaluationWordAction;
use Quochao56\PlanningEvaluation\Filament\Actions\ReopenAction;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;

class EditEvaluation extends EditRecord
{
    use HasAutoSave;

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
            'data.evaluation_details.*.muc_tieu.*.danh_gia' => ['nullable', 'required_if:data.status,'.BaseStatusEnum::Published->value],
        ];
    }

    protected function getValidationMessages(): array
    {
        return [
            'data.evaluation_details.*.muc_tieu.*.danh_gia.required_if' => trans('packages.planning_evaluation::evaluation.validation.danh_gia_required_when_published'),
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
            ApproveAction::make(),
            ReopenAction::make(),
            ActionGroup::make([
                ExportEvaluationWordAction::make(),
                DeleteAction::make(),
            ])
                ->label('Thao tác')
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
            $this->getSaveFormAction()
                ->submit(null)
                ->action(fn () => $this->save())
                ->keyBindings(['mod+s']),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        $record = $parameters['record'] ?? null;

        if ($record) {
            return static::getResource()::canEdit($record) || static::getResource()::canView($record);
        }

        return parent::canAccess($parameters);
    }

    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();
        if (! static::getResource()::canEdit($record)) {
            if (static::getResource()::canView($record)) {
                $this->redirect(static::getResource()::getUrl('view', [
                    'planning' => $record->planning_id,
                    'record' => $record,
                ]));

                return;
            }

            abort(403);
        }
    }

    protected function getRedirectUrl(): ?string
    {
        $record = $this->getRecord();
        if (($record?->status?->value ?? $record?->status) === BaseStatusEnum::Published->value) {
            return static::getResource()::getUrl('view', [
                'planning' => $record->planning_id,
                'record' => $record,
            ]);
        }

        return parent::getRedirectUrl();
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
                    $message = trans('packages.planning_evaluation::evaluation.validation.danh_gia_required_when_published');
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
