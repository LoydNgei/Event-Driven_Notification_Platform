<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\ChannelManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     * Exponential backoff: 1 min, 5 min, 15 min
     */
    public array $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public NotificationLog $notificationLog
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ChannelManager $channelManager): void
    {
        $log = $this->notificationLog;
        
        // Mark as processing
        $log->markAsProcessing();

        try {
            // Load the rule and template
            $rule = $log->notificationRule;
            $template = $rule->notificationTemplate;

            // Render the template content
            $content = $template->renderBody($log->payload);
            $subject = $template->renderSubject($log->payload);

            // Get the appropriate channel handler
            $channel = $channelManager->driver($log->channel);

            // Send the notification
            $channel->send($log, $subject, $content);

            // Mark as sent
            $log->markAsSent();

            Log::info("Notification sent successfully", [
                'log_id' => $log->id,
                'channel' => $log->channel,
                'recipient' => $log->recipient,
            ]);

        } catch (Throwable $e) {
            $log->markAsFailed($e->getMessage());

            Log::error("Notification failed", [
                'log_id' => $log->id,
                'channel' => $log->channel,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->notificationLog->markAsFailed(
            $exception?->getMessage() ?? 'Unknown error'
        );

        Log::error("Notification job failed permanently", [
            'log_id' => $this->notificationLog->id,
            'attempts' => $this->notificationLog->attempts,
            'error' => $exception?->getMessage(),
        ]);
    }
}
