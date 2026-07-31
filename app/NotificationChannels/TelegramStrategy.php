<?php

namespace App\NotificationChannels;

use App\Models\Notification;

class TelegramStrategy implements ChannelStrategy
{
    public function send(Notification $notification): void
    {
        // TODO: Stub telegram sending
    }
}
