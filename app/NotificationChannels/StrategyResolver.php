<?php

namespace App\NotificationChannels;

use App\Enums\NotificationChannel;

class StrategyResolver
{
    public function resolve(NotificationChannel $channel): ChannelStrategy
    {
        return match ($channel) {
            NotificationChannel::Email => app(EmailStrategy::class),
            NotificationChannel::Telegram => app(TelegramStrategy::class),
        };
    }
}
