<?php

namespace App\NotificationChannels;

use App\Models\Notification;

interface ChannelStrategy
{
    public function send(Notification $notification): void;
}
