<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Notification;
use App\NotificationChannels\StrategyResolver;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $notificationId,
    ) {

    }

    /**
     * Execute the job.
     */
    public function handle(StrategyResolver $strategyResolver): void
    {
        $notification = Notification::findOrFail($this->notificationId);
        $notification->registerAttempt();

        try {
            $strategyResolver
                ->resolve($notification->channel)
                ->send($notification);

            $notification->markAsSent();
        } catch (\Throwable $e) {
            $error = $e->getMessage();

            if ($notification->attempts < $notification::MAX_ATTEMPTS) {
                $notification->markAsDeferred($error);
            } else {
                $notification->markAsFailed($error);
            }
        }
    }
}
