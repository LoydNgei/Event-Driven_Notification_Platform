<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationRuleController extends Controller
{
    /**
     * Display a listing of notification rules.
     */
    public function index(Request $request): JsonResponse
    {
        $query = NotificationRule::with(['eventSource', 'notificationTemplate']);

        if ($request->has('event_source_id')) {
            $query->where('event_source_id', $request->input('event_source_id'));
        }

        if ($request->has('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $rules = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $rules,
        ]);
    }

    /**
     * Store a newly created notification rule.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_source_id' => 'required|exists:event_sources,id',
            'notification_template_id' => 'required|exists:notification_templates,id',
            'channel' => 'required|in:email,sms,slack',
            'conditions' => 'nullable|array',
            'recipient_email' => 'nullable|email',
            'recipient_phone' => 'nullable|string|max:20',
            'recipient_slack_webhook' => 'nullable|url',
            'recipient_field' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $rule = NotificationRule::create($validated);

        return response()->json([
            'message' => 'Notification rule created successfully.',
            'data' => $rule->load(['eventSource', 'notificationTemplate']),
        ], 201);
    }

    /**
     * Display the specified notification rule.
     */
    public function show(NotificationRule $notificationRule): JsonResponse
    {
        return response()->json([
            'data' => $notificationRule->load(['eventSource', 'notificationTemplate']),
        ]);
    }

    /**
     * Update the specified notification rule.
     */
    public function update(Request $request, NotificationRule $notificationRule): JsonResponse
    {
        $validated = $request->validate([
            'event_source_id' => 'sometimes|exists:event_sources,id',
            'notification_template_id' => 'sometimes|exists:notification_templates,id',
            'channel' => 'sometimes|in:email,sms,slack',
            'conditions' => 'nullable|array',
            'recipient_email' => 'nullable|email',
            'recipient_phone' => 'nullable|string|max:20',
            'recipient_slack_webhook' => 'nullable|url',
            'recipient_field' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $notificationRule->update($validated);

        return response()->json([
            'message' => 'Notification rule updated successfully.',
            'data' => $notificationRule->load(['eventSource', 'notificationTemplate']),
        ]);
    }

    /**
     * Remove the specified notification rule.
     */
    public function destroy(NotificationRule $notificationRule): JsonResponse
    {
        $notificationRule->delete();

        return response()->json([
            'message' => 'Notification rule deleted successfully.',
        ]);
    }
}
