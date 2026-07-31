<?php

namespace Tests\Feature;

use App\Enums\NotificationStatus;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_attempt_increments_attempts_and_sets_last_attempt_at(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'attempts' => 0,
        ]);

        $notification->registerAttempt();

        $this->assertEquals(1, $notification->attempts);
        $this->assertNotNull($notification->last_attempt_at);
    }

    public function test_mark_as_processing_updates_status(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
        ]);

        $notification->markAsProcessing();

        $this->assertEquals(
            NotificationStatus::Processing,
            $notification->status
        );
    }

    public function test_mark_as_sent_updates_status_and_delivery_date(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
        ]);

        $notification->markAsSent();

        $this->assertEquals(
            NotificationStatus::Sent,
            $notification->status
        );

        $this->assertNotNull($notification->delivered_at);
    }

    public function test_mark_as_deferred_updates_status_and_error(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
        ]);

        $notification->markAsDeferred('SMTP unavailable');

        $this->assertEquals(
            NotificationStatus::Pending,
            $notification->status
        );

        $this->assertEquals(
            'SMTP unavailable',
            $notification->last_error
        );
    }

    public function test_mark_as_failed_updates_status_and_error(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
        ]);

        $notification->markAsFailed('SMTP unavailable');

        $this->assertEquals(
            NotificationStatus::Failed,
            $notification->status
        );

        $this->assertEquals(
            'SMTP unavailable',
            $notification->last_error
        );
    }
}
