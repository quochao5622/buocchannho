<?php

namespace Quochao56\Acl\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        if ($event->user) {
            activity()
                ->causedBy($event->user)
                ->event('login')
                ->log(trans('acl::activity.events.login'));
        }
    }
}
