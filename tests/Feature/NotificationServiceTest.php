<?php

namespace Tests\Feature;

use App\Dto\NotificationData;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_notification_and_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $data = new NotificationData(
            $user->id,
            NotificationChannel::Email,
            'Test notification'
        );

        $notification = app(NotificationService::class)
            ->create($data);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'status' => NotificationStatus::Pending->value,
            'message' => 'Test notification',
            'attempts' => 0,
        ]);

        Queue::assertPushed(
            SendNotificationJob::class,
            function ($job) use ($notification) {
                return $job->notificationId === $notification->id;
            }
        );
    }
}
