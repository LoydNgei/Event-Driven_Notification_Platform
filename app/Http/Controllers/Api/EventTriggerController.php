<?php

namespace App\Http\Controllers\Api;

use App\Events\EventTriggered;
use App\Http\Controllers\Controller;
use App\Models\EventSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventTriggerController extends Controller
{
    /**
     * Trigger an event and dispatch notifications.
     *
     * POST /api/events
     * {
     *   "event": "order_created",
     *   "payload": { "order_id": 444 }
     * }
     */
    public function trigger(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event' => 'required|string|max:255',
            'payload' => 'nullable|array',
        ]);

        $eventName = $validated['event'];
        $payload = $validated['payload'] ?? [];

        // Find the event source
        $eventSource = EventSource::where('name', $eventName)
            ->where('is_active', true)
            ->first();

        if (!$eventSource) {
            return response()->json([
                'error' => 'Event source not found or inactive.',
                'event' => $eventName,
            ], 404);
        }

        // Dispatch the Laravel event
        event(new EventTriggered($eventSource, $payload));

        return response()->json([
            'message' => 'Event triggered successfully.',
            'event' => $eventName,
            'payload' => $payload,
        ]);
    }
}
