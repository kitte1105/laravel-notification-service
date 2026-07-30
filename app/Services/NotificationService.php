<?php

namespace App\Services;

use App\Dto\NotificationData;
use App\Models\Notification;
use App\Enums\NotificationStatus;

class NotificationService
{
    public function create(NotificationData $notificationData) : Notification
    {
        $notification = new Notification();
        $notification->fill($notificationData->toArray());
        $notification->status = NotificationStatus::Processing;
        $notification->attempts = 0;
        $notification->save();

        return $notification;
    }
}
