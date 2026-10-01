<?php

namespace Quochao56\Core\Traits;

use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

/**
 * Trang Create không có autosave (chưa có record để lưu), nên nhắc người dùng
 * lưu lại khi họ đã nhập dữ liệu một thời gian mà chưa bấm "Tạo".
 * Chạy hoàn toàn phía trình duyệt, không gửi request Livewire nào.
 */
trait HasCreateReminder
{
    protected function getCreateReminderMinutes(): int
    {
        return 10;
    }

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        $components = $schema->getComponents();

        $components[] = View::make('filament.create-reminder')
            ->viewData([
                'minutes' => $this->getCreateReminderMinutes(),
                'title' => trans('packages.core::core.messages.create_reminder.title'),
                'body' => trans('packages.core::core.messages.create_reminder.body'),
            ]);

        return $schema->components($components);
    }
}
