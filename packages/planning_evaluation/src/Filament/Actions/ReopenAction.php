<?php

namespace Quochao56\PlanningEvaluation\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Quochao56\Core\Enum\BaseStatusEnum;

class ReopenAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reopen';
    }

    public function handle(Model $record): void
    {
        try {
            $record->update(['status' => BaseStatusEnum::Draft]);

            Notification::make()
                ->title('Đã mở lại bản nháp thành công!')
                ->success()
                ->send();

            if ($this->getLivewire()) {
                $this->getLivewire()->dispatch('notificationsSent');
            }
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Đã xảy ra lỗi khi mở lại bản nháp.')
                ->danger()
                ->send();

            if ($this->getLivewire()) {
                $this->getLivewire()->dispatch('notificationsSent');
            }
            Log::error($th);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Mở lại bản nháp');
        $this->color('warning');
        $this->icon(Heroicon::OutlinedArrowUturnLeft);
        $this->requiresConfirmation();
        $this->modalHeading('Mở lại bản nháp');
        $this->modalDescription('Bạn có chắc chắn muốn chuyển trạng thái về bản nháp để tiếp tục chỉnh sửa không?');
        $this->modalSubmitActionLabel('Xác nhận');
        $this->authorize('reopen');

        $this->action(fn (Model $record) => $this->handle($record));
    }
}
