<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    /**
     * Display a listing of notification logs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = NotificationLog::with(['eventSource', 'notificationRule']);

        if ($request->has('event_source_id')) {
            $query->where('event_source_id', $request->input('event_source_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        $perPage = min($request->input('per_page', 15), 100);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Display the specified notification log.
     */
    public function show(NotificationLog $notificationLog): JsonResponse
    {
        return response()->json([
            'data' => $notificationLog->load(['eventSource', 'notificationRule.notificationTemplate']),
        ]);
    }
}
