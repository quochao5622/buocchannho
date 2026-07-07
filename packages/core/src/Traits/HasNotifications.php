<?php

namespace Quochao56\Core\Traits;

use Filament\Notifications\Notification;

trait HasNotifications
{
    /**
     * Gửi notification và đảm bảo dispatch event để Filament Notifications
     * component pull từ session ngay lập tức (không cần reload trang).
     */
    protected function notify(Notification $notification): void
    {
        $notification->send();
        $this->dispatch('notificationsSent');
    }
}
