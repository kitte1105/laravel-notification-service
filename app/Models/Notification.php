<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\NotificationStatus;
use App\Enums\NotificationChannel;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;
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
        ];
    }
}
