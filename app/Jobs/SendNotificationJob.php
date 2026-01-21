<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Enums\NotificationStatus;
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

    public int $tries = 3;
    public array $backoff = [60, 300, 900];
    
    public function __construct(
        public NotificationLog $notificationLogId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ChannelManager $channelManager): void
    {
        $log = NotificationLog::query()
            ->with(['notificationRule.notificationTemplate'])
            ->findOrFail($this->notificationLogId);

        // Idempotency guard: if already sent, do nothing
        if ($log->status === NotificationStatus::Sent->value) {
            return;
        }

        //  Claim the log so two workers don’t send twice
        $claimed = NotificationLog::query()
            ->whereKey($log->id)
            ->whereIn('status', [
                NotificationStatus::Pending->value,
                NotificationStatus::Failed->value,
            ])
            ->update([
                'status' => NotificationStatus::Processing->value,
                'attempts' => $log->attempts + 1,
            ]);

        if ($claimed === 0) {
            return;
        }

        // Refresh to have the latest
        $log->refresh();

        try {
            // Load the rule and template
            $rule = $log->notificationRule;
            $template = $rule->notificationTemplate;

            // Render the template content
            $content = $template->renderBody($log->payload);
            $subject = $template->renderSubject($log->payload);

            // Get the appropriate channel handler & send notification
            $channel = $channelManager->driver($log->channel);
            $channel->send($log, $subject, $content);

            $log->markAsSent();


        } catch (Throwable $e) {
            $log->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
       NotificationLog::whereKey($this->notificationLogId)->update([
            'status' => NotificationStatus::Failed->value,
            'error_message' => $exception?->getMessage() ?? 'Unknown error',
        ]);
    }
}
