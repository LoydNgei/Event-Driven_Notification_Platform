<?php

namespace App\Listeners;

use App\Enums\NotificationStatus;
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

        $rules = $eventSource->activeRules()
            ->with('notificationTemplate')
            ->get();

        foreach ($rules as $rule) {
            if (!$rule->matchesConditions($payload)) {
                continue;
            }

            // Get recipient based on rule configuration
            $recipient = $rule->getRecipient($payload);

            if (!$recipient) {
                continue;
            }

            $log = NotificationLog::create([
                'event_source_id' => $eventSource->id,
                'notification_rule_id' => $rule->id,
                'channel' => $rule->channel,
                'payload' => $payload,
                'recipient' => $recipient,
                'status' => NotificationStatus::Pending->value,
            ]);

            SendNotificationJob::dispatch($log->id);
        }
    }
}
