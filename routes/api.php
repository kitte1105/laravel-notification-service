<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('notifications', NotificationController::class)
    ->only(['index', 'store', 'show']);
