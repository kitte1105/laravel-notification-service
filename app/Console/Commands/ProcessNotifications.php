<?php

namespace App\Console\Commands;

use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:process-notifications')]
#[Description('Process pending notifications')]
class ProcessNotifications extends Command
{
    public function handle(): int
    {
        Notification::readyToSend()
            ->chunkById(100, function ($notifications) {
                foreach ($notifications as $notification) {
                    $notification->markAsProcessing();

                    SendNotificationJob::dispatch($notification->id);
                }
            });

        return self::SUCCESS;
    }
}
