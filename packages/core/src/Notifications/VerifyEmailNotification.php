<?php

namespace Quochao56\Core\Notifications;

use Filament\Auth\Notifications\VerifyEmail as FilamentVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends FilamentVerifyEmail
{
    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  mixed  $url
     * @return MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Xác minh địa chỉ Email')
            ->line('Vui lòng nhấp vào nút bên dưới để xác minh địa chỉ email của bạn.')
            ->action('Xác minh Email', $url)
            ->line('Nếu bạn không tạo tài khoản, bạn không cần thực hiện thêm hành động nào.');
    }
}
