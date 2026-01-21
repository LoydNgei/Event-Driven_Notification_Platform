<?php

use App\Http\Controllers\Api\EventSourceController;
use App\Http\Controllers\Api\EventTriggerController;
use App\Http\Controllers\Api\NotificationLogController;
use App\Http\Controllers\Api\NotificationRuleController;
use App\Http\Controllers\Api\NotificationTemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Event Sources CRUD
Route::apiResource('event-sources', EventSourceController::class);

// Trigger events - the main endpoint
Route::post('events', [EventTriggerController::class, 'trigger']);

// Notification Templates CRUD
Route::apiResource('notification-templates', NotificationTemplateController::class);

// Notification Rules CRUD
Route::apiResource('notification-rules', NotificationRuleController::class);

// Notification Logs (read-only)
Route::get('notification-logs', [NotificationLogController::class, 'index']);
Route::get('notification-logs/{notificationLog}', [NotificationLogController::class, 'show']);
