<?php

namespace App\Dto;

use App\Enums\NotificationChannel;

readonly class NotificationData
{
    public function __construct(
        public int $userId,
        public NotificationChannel $channel,
        public string $message,
    ) {
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'channel' => $this->channel,
            'message' => $this->message,
        ];
    }
}
