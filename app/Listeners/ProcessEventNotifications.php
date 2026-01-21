<?php

namespace App\Listeners;

use App\Events\EventTriggered;
use App\Jobs\SendNotificationJob;
use App\Models\NotificationLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessEventNotifications implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(EventTriggered $event): void
    {
        $eventSource = $event->eventSource;
        $payload = $event->payload;

        // Get all active rules for this event source
        $rules = $eventSource->activeRules()
            ->with('notificationTemplate')
            ->get();

        foreach ($rules as $rule) {
            // Check if payload matches the rule conditions
            if (!$rule->matchesConditions($payload)) {
                continue;
            }

            // Get recipient based on rule configuration
            $recipient = $rule->getRecipient($payload);

            // Create a notification log entry
            $log = NotificationLog::create([
                'event_source_id' => $eventSource->id,
                'notification_rule_id' => $rule->id,
                'channel' => $rule->channel,
                'payload' => $payload,
                'recipient' => $recipient,
                'status' => NotificationLog::STATUS_PENDING,
            ]);

            // Dispatch the job to send the notification
            SendNotificationJob::dispatch($log);
        }
    }
}
