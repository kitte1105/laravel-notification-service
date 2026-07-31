<?php

namespace Tests\Feature;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_notifications_dispatches_ready_notifications(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Pending,
        ]);

        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Sent,
        ]);

        $this->artisan('app:process-notifications')
            ->assertSuccessful();

        Queue::assertPushed(SendNotificationJob::class, 3);

        $this->assertDatabaseCount('notifications', 5);

        $this->assertEquals(
            3,
            Notification::where('status', NotificationStatus::Processing)->count()
        );

        $this->assertEquals(
            2,
            Notification::where('status', NotificationStatus::Sent)->count()
        );
    }
}
