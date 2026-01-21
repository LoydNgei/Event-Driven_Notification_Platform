<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of notification templates.
     */
    public function index(Request $request): JsonResponse
    {
        $query = NotificationTemplate::query();

        if ($request->has('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        $templates = $query->orderBy('name')->get();

        return response()->json([
            'data' => $templates,
        ]);
    }

    /**
     * Store a newly created notification template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:email,sms,slack',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $template = NotificationTemplate::create($validated);

        return response()->json([
            'message' => 'Notification template created successfully.',
            'data' => $template,
        ], 201);
    }

    /**
     * Display the specified notification template.
     */
    public function show(NotificationTemplate $notificationTemplate): JsonResponse
    {
        return response()->json([
            'data' => $notificationTemplate,
        ]);
    }

    /**
     * Update the specified notification template.
     */
    public function update(Request $request, NotificationTemplate $notificationTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'channel' => 'sometimes|in:email,sms,slack',
            'subject' => 'nullable|string|max:255',
            'body' => 'sometimes|string',
        ]);

        $notificationTemplate->update($validated);

        return response()->json([
            'message' => 'Notification template updated successfully.',
            'data' => $notificationTemplate,
        ]);
    }

    /**
     * Remove the specified notification template.
     */
    public function destroy(NotificationTemplate $notificationTemplate): JsonResponse
    {
        $notificationTemplate->delete();

        return response()->json([
            'message' => 'Notification template deleted successfully.',
        ]);
    }
}
