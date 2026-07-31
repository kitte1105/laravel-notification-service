<?php

namespace Tests\Unit;

use App\Dto\NotificationData;
use App\Enums\NotificationChannel;
use Tests\TestCase;

class NotificationDataTest extends TestCase
{
    public function test_converts_notification_data_to_array(): void
    {
        $notificationData = new NotificationData(
            1,
            NotificationChannel::Email,
            'Test message'
        );

        $this->assertEquals([
            'user_id' => 1,
            'channel' => NotificationChannel::Email,
            'message' => 'Test message',
        ], $notificationData->toArray());
    }
}
