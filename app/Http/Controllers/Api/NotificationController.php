<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Dto\NotificationData;
use App\Services\NotificationService;
use App\Enums\NotificationChannel;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function store(
        StoreNotificationRequest $request,
        NotificationService $notificationService
    ): JsonResponse {
        $data = $request->validated();

        $notification = $notificationService->create(
            new NotificationData(
                userId: $data['user_id'],
                channel: NotificationChannel::from($data['channel']),
                message: $data['message'],
            )
        );

        return response()->json($notification, 201);
    }

    public function show(Notification $notification): JsonResponse
    {
        return response()->json($notification);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Notification::query();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->string('channel'));
        }

        $notifications = $query
            ->orderByDesc('created_at')
            ->get();

        return response()->json($notifications);
    }
}
