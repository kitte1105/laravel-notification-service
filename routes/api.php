<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;

Route::apiResource('notifications', NotificationController::class)
    ->only(['index', 'store', 'show']);
