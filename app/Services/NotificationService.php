<?php

namespace App\Services;

use App\Dto\NotificationData;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;

class NotificationService
{
    public function create(NotificationData $notificationData): Notification
    {
        $notification = new Notification;
        $notification->fill($notificationData->toArray());
        $notification->status = NotificationStatus::Pending;
        $notification->attempts = 0;
        $notification->save();

        SendNotificationJob::dispatch($notification->id);

        return $notification;
    }
}
