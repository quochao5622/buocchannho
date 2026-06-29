<?php

namespace Quochao56\Acl\Listeners;

use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        if ($event->user) {
            activity()
                ->causedBy($event->user)
                ->event('logout')
                ->log(trans('acl::activity.events.logout'));
        }
    }
}
