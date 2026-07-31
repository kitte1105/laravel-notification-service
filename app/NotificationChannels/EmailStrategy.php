<?php

namespace App\NotificationChannels;

use App\Models\Notification;

class EmailStrategy implements ChannelStrategy
{
    public function send(Notification $notification): void
    {
        // TODO: Stub email sending
    }
}
