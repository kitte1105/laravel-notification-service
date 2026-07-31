<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Carbon\Carbon;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property NotificationStatus $status
 * @property NotificationChannel $channel
 * @property int $attempts
 * @property string|null $last_error
 * @property Carbon|null $delivered_at
 * @property Carbon|null $last_attempt_at
 * @property Carbon|null $processing_started_at
 */
class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    const MAX_ATTEMPTS = 5;

    const DEFERRED_MINUTES = 5;

    protected $fillable = [
        'user_id',
        'channel',
        'status',
        'message',
        'attempts',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'status' => NotificationStatus::class,
            'channel' => NotificationChannel::class,
            'delivered_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'processing_started_at' => 'datetime',
        ];
    }

    public function scopeReadyToSend($query)
    {
        return $query->where(function ($query) {
            $query
                ->where('status', NotificationStatus::Pending)
                ->where(function ($query) {
                    $query
                        ->whereNull('last_attempt_at')
                        ->orWhere(
                            'last_attempt_at',
                            '<=',
                            now()->subMinutes(self::DEFERRED_MINUTES)
                        );
                });
        })->orWhere(function ($query) {
            $query
                ->where('status', NotificationStatus::Processing)
                ->where(
                    'processing_started_at',
                    '<=',
                    now()->subMinutes(self::DEFERRED_MINUTES * 2)
                );
        });
    }

    public function markAsProcessing(): void
    {
        $this->status = NotificationStatus::Processing;
        $this->processing_started_at = now();
        $this->save();
    }

    public function markAsSent(): void
    {
        $this->status = NotificationStatus::Sent;
        $this->delivered_at = now();
        $this->last_error = null;
        $this->processing_started_at = null;
        $this->save();
    }

    public function markAsDeferred(string $error): void
    {
        $this->status = NotificationStatus::Pending;
        $this->last_error = $error;
        $this->processing_started_at = null;
        $this->save();
    }

    public function markAsFailed(string $error): void
    {
        $this->status = NotificationStatus::Failed;
        $this->last_error = $error;
        $this->processing_started_at = null;
        $this->save();
    }

    public function registerAttempt(): void
    {
        $this->attempts++;
        $this->last_attempt_at = now();
        $this->save();
    }
}
