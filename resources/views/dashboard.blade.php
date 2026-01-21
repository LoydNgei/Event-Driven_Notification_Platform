<x-layout title="Dashboard">
    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Events</div>
            <div class="stat-value">{{ $eventSources->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Rules</div>
            <div class="stat-value">{{ $activeRules }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Notifications Sent</div>
            <div class="stat-value">{{ $sentCount }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Failed</div>
            <div class="stat-value" style="color: var(--danger);">{{ $failedCount }}</div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Recent Notifications -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Notifications</h2>
                <a href="/api/notification-logs" class="btn btn-secondary">View All</a>
            </div>

            @if($recentLogs->isEmpty())
                <div class="empty-state">
                    <p>No notifications yet. Trigger an event to see notifications here.</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                            <tr>
                                <td>
                                    <span class="channel-icon">
                                        @if($log->channel === 'email') 📧
                                        @elseif($log->channel === 'sms') 📱
                                        @else 💬
                                        @endif
                                        {{ ucfirst($log->channel) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $log->status }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Quick Event Trigger -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quick Event Trigger</h2>
            </div>

            <form action="/dashboard/trigger" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="event">Event Name</label>
                    <select class="form-select" name="event" id="event" required>
                        <option value="">Select an event...</option>
                        @foreach($eventSources as $source)
                            <option value="{{ $source->name }}">{{ $source->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="payload">Payload (JSON)</label>
                    <textarea
                        class="form-textarea"
                        name="payload"
                        id="payload"
                        placeholder='{"order_id": 444, "customer_email": "test@example.com"}'
                    >{}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Trigger Event</button>
            </form>

            <!-- API Example -->
            <div style="margin-top: 1.5rem;">
                <p class="form-label">API Equivalent:</p>
                <div class="code-block">
POST /api/events
{
  "event": "order_created",
  "payload": { "order_id": 444 }
}
                </div>
            </div>
        </div>
    </div>

    <!-- Event Sources -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Event Sources</h2>
            <a href="/api/event-sources" class="btn btn-secondary">API</a>
        </div>

        @if($eventSources->isEmpty())
            <div class="empty-state">
                <p>No event sources configured.</p>
                <p style="margin-top: 0.5rem; font-size: 0.875rem;">
                    Create one via API: <code>POST /api/event-sources</code>
                </p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Rules</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eventSources as $source)
                        <tr>
                            <td><strong>{{ $source->name }}</strong></td>
                            <td>{{ $source->description ?? '-' }}</td>
                            <td>{{ $source->notificationRules->count() }} rules</td>
                            <td>
                                <span class="badge {{ $source->is_active ? 'badge-sent' : 'badge-pending' }}">
                                    {{ $source->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layout>
