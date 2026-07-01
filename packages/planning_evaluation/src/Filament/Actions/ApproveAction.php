<?php

namespace Quochao56\PlanningEvaluation\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\PlanningEvaluation\Models\Evaluation;

class ApproveAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'approve';
    }

    public function handle(Model $record): void
    {
        try {
            if ($record instanceof Evaluation) {
                // Check if all goals have 'danh_gia' set
                foreach ((array) ($record->evaluation_details ?? []) as $row) {
                    foreach ((array) ($row['muc_tieu'] ?? []) as $goal) {
                        if (blank($goal['danh_gia'] ?? null)) {
                            Notification::make()
                                ->title('Không thể duyệt vì một số mục tiêu chưa được đánh giá.')
                                ->danger()
                                ->send();

                            return;
                        }
                    }
                }
            }

            $record->update(['status' => BaseStatusEnum::Published]);

            Notification::make()
                ->title('Đã duyệt thành công!')
                ->success()
                ->send();
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Đã xảy ra lỗi khi duyệt.')
                ->danger()
                ->send();
            Log::error($th);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Duyệt');
        $this->color('success');
        $this->requiresConfirmation();
        $this->authorize('approve');

        $this->action(fn (Model $record) => $this->handle($record));
    }
}
