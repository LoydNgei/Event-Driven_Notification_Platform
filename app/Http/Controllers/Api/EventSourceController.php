<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventSourceController extends Controller
{
    /**
     * Display a listing of event sources.
     */
    public function index(Request $request): JsonResponse
    {
        $query = EventSource::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $eventSources = $query->orderBy('name')->get();

        return response()->json([
            'data' => $eventSources,
        ]);
    }

    /**
     * Store a newly created event source.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_sources,name',
            'description' => 'nullable|string|max:500',
            'schema' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $eventSource = EventSource::create($validated);

        return response()->json([
            'message' => 'Event source created successfully.',
            'data' => $eventSource,
        ], 201);
    }

    /**
     * Display the specified event source.
     */
    public function show(EventSource $eventSource): JsonResponse
    {
        return response()->json([
            'data' => $eventSource->load(['notificationRules.notificationTemplate']),
        ]);
    }

    /**
     * Update the specified event source.
     */
    public function update(Request $request, EventSource $eventSource): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:event_sources,name,' . $eventSource->id,
            'description' => 'nullable|string|max:500',
            'schema' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $eventSource->update($validated);

        return response()->json([
            'message' => 'Event source updated successfully.',
            'data' => $eventSource,
        ]);
    }

    /**
     * Remove the specified event source.
     */
    public function destroy(EventSource $eventSource): JsonResponse
    {
        $eventSource->delete();

        return response()->json([
            'message' => 'Event source deleted successfully.',
        ]);
    }
}
