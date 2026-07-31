<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Support\Facades\Queue;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_notification_successfully(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
            'message' => 'Test notification',
        ];

        $response = $this->postJson('/api/notifications', $data);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'status',
            ]);

        $this->assertDatabaseHas('notifications', [
            'user_id'  => $user->id,
            'channel'  => NotificationChannel::Email->value,
            'status'   => NotificationStatus::Pending->value,
            'message'  => 'Test notification',
            'attempts' => 0,
        ]);
    }

    public function test_returns_notification_by_id(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
            'message' => 'Test notification',
        ]);

        $response = $this->getJson("/api/notifications/{$notification->id}");

        $response
            ->assertOk()
            ->assertJson([
                'id' => $notification->id,
                'user_id' => $user->id,
                'channel' => NotificationChannel::Email->value,
                'status' => NotificationStatus::Pending->value,
                'message' => 'Test notification',
                'attempts' => 0,
            ]);
    }

    public function test_returns_all_notifications(): void
    {
        $user = User::factory()->create();

        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
            'message' => 'Test notification',
        ]);

        $response = $this->getJson("/api/notifications");

        $response
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_returns_notifications_for_specific_user(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        Notification::factory()->count(3)->create([
            'user_id' => $firstUser->id,
        ]);

        Notification::factory()->count(2)->create([
            'user_id' => $secondUser->id,
        ]);

        $response = $this->getJson("/api/notifications?user_id={$firstUser->id}");

        $response
            ->assertOk()
            ->assertJsonCount(3);

        foreach ($response->json() as $notification) {
            $this->assertEquals($firstUser->id, $notification['user_id']);
        }
    }

    public function test_filters_notifications_by_status(): void
    {
        $user = User::factory()->create();

        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => NotificationStatus::Failed,
        ]);

        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => NotificationStatus::Pending,
        ]);

        Notification::factory()->count(4)->create([
            'user_id' => $user->id,
            'status' => NotificationStatus::Sent,
        ]);

        $response = $this->getJson('/api/notifications?status='. NotificationStatus::Pending->value);

        $response
            ->assertOk()
            ->assertJsonCount(3);

        foreach ($response->json() as $notification) {
            $this->assertEquals(NotificationStatus::Pending->value, $notification['status']);
        }
    }

    public function test_filters_notifications_by_channel(): void
    {
        $user = User::factory()->create();

        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
        ]);

        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Telegram,
        ]);

        $response = $this->getJson('/api/notifications?channel='. NotificationChannel::Telegram->value);

        $response
            ->assertOk()
            ->assertJsonCount(3);

        foreach ($response->json() as $notification) {
            $this->assertEquals(NotificationChannel::Telegram->value, $notification['channel']);
        }
    }

    public function test_filters_notifications_by_user_status_and_channel(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        Notification::factory()->count(2)->create([
            'user_id' => $firstUser->id,
            'status' => NotificationStatus::Pending,
            'channel' => NotificationChannel::Email,
        ]);

        Notification::factory()->count(4)->create([
            'user_id' => $firstUser->id,
            'status' => NotificationStatus::Pending,
            'channel' => NotificationChannel::Telegram,
        ]);

        Notification::factory()->count(5)->create([
            'user_id' => $firstUser->id,
            'status' => NotificationStatus::Sent,
            'channel' => NotificationChannel::Email,
        ]);

        Notification::factory()->count(3)->create([
            'user_id' => $firstUser->id,
            'status' => NotificationStatus::Sent,
            'channel' => NotificationChannel::Telegram,
        ]);

        Notification::factory()->count(2)->create([
            'user_id' => $secondUser->id,
            'status' => NotificationStatus::Pending,
            'channel' => NotificationChannel::Telegram,
        ]);

        $response = $this->getJson('/api/notifications?' . http_build_query([
                'user_id' => $firstUser->id,
                'status' => NotificationStatus::Sent->value,
                'channel' => NotificationChannel::Telegram->value,
            ]));

        $response
            ->assertOk()
            ->assertJsonCount(3);

        foreach ($response->json() as $notification) {
            $this->assertEquals($firstUser->id, $notification['user_id']);
            $this->assertEquals(NotificationStatus::Sent->value, $notification['status']);
            $this->assertEquals(NotificationChannel::Telegram->value, $notification['channel']);
        }
    }
}
