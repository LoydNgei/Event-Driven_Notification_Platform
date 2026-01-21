<?php

namespace App\Http\Controllers;

use App\Models\EventSource;
use App\Models\NotificationLog;
use App\Models\NotificationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index()
    {
        $eventSources = EventSource::with('notificationRules')->get();
        $recentLogs = NotificationLog::with(['eventSource'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', [
            'eventSources' => $eventSources,
            'recentLogs' => $recentLogs,
            'activeRules' => NotificationRule::where('is_active', true)->count(),
            'sentCount' => NotificationLog::where('status', 'sent')->count(),
            'failedCount' => NotificationLog::where('status', 'failed')->count(),
        ]);
    }

    /**
     * Trigger an event from the dashboard form.
     */
    public function trigger(Request $request)
    {
        $request->validate([
            'event' => 'required|string',
            'payload' => 'nullable|string',
        ]);

        $payload = [];
        if ($request->payload) {
            $payload = json_decode($request->payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Invalid JSON payload: ' . json_last_error_msg());
            }
        }

        // Call the internal API
        $response = Http::post(url('/api/events'), [
            'event' => $request->event,
            'payload' => $payload,
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Event triggered successfully! Check the notification logs.');
        }

        return back()->with('error', 'Failed to trigger event: ' . $response->json('error', 'Unknown error'));
    }
}
