<?php

namespace Tests\Unit;

use App\Enums\NotificationChannel;
use App\NotificationChannels\EmailStrategy;
use App\NotificationChannels\StrategyResolver;
use App\NotificationChannels\TelegramStrategy;
use Tests\TestCase;

class StrategyResolverTest extends TestCase
{
    public function test_resolves_email_strategy(): void
    {
        $resolver = new StrategyResolver;

        $strategy = $resolver->resolve(NotificationChannel::Email);

        $this->assertInstanceOf(
            EmailStrategy::class,
            $strategy
        );
    }

    public function test_resolves_telegram_strategy(): void
    {
        $resolver = new StrategyResolver;

        $strategy = $resolver->resolve(NotificationChannel::Telegram);

        $this->assertInstanceOf(
            TelegramStrategy::class,
            $strategy
        );
    }
}
