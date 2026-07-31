<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use App\Enums\NotificationStatus;
use App\Enums\NotificationChannel;
use App\Jobs\SendNotificationJob;
use App\NotificationChannels\StrategyResolver;
use App\NotificationChannels\ChannelStrategy;
use Mockery;
use Exception;

class SendNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_notification_job_marks_notification_as_sent(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Telegram,
            'status' => NotificationStatus::Pending,
            'delivered_at' => null,
        ]);

        $job = new SendNotificationJob($notification->id);
        $job->handle(new StrategyResolver());

        $notification->refresh();

        $this->assertEquals(NotificationStatus::Sent, $notification->status);
        $this->assertNotNull($notification->delivered_at);
    }

    public function test_marks_notification_as_deferred_when_send_fails_and_attempts_remain(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Pending,
            'attempts' => 0,
        ]);

        $strategy = Mockery::mock(ChannelStrategy::class);
        $strategy->shouldReceive('send')
            ->once()
            ->andThrow(new Exception('SMTP unavailable'));

        $resolver = Mockery::mock(StrategyResolver::class);
        $resolver->shouldReceive('resolve')
            ->once()
            ->andReturn($strategy);

        $job = new SendNotificationJob($notification->id);
        $job->handle($resolver);

        $notification->refresh();

        $this->assertEquals(NotificationStatus::Pending, $notification->status);
        $this->assertEquals(1, $notification->attempts);
        $this->assertEquals('SMTP unavailable', $notification->last_error);
        $this->assertNotNull($notification->last_attempt_at);
    }

    public function test_marks_notification_as_failed_when_max_attempts_reached(): void
    {
        $user = User::factory()->create();

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'channel' => NotificationChannel::Email,
            'status' => NotificationStatus::Pending,
            'attempts' => Notification::MAX_ATTEMPTS - 1,
        ]);

        $strategy = Mockery::mock(ChannelStrategy::class);
        $strategy->shouldReceive('send')
            ->once()
            ->andThrow(new Exception('SMTP unavailable'));

        $resolver = Mockery::mock(StrategyResolver::class);
        $resolver->shouldReceive('resolve')
            ->once()
            ->andReturn($strategy);

        $job = new SendNotificationJob($notification->id);
        $job->handle($resolver);

        $notification->refresh();

        $this->assertEquals(NotificationStatus::Failed, $notification->status);
        $this->assertEquals(Notification::MAX_ATTEMPTS, $notification->attempts);
        $this->assertEquals('SMTP unavailable', $notification->last_error);
        $this->assertNotNull($notification->last_attempt_at);
    }
}
