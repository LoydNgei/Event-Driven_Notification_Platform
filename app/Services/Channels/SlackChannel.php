<?php

namespace App\Services\Channels;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackChannel implements NotificationChannelInterface
{
    /**
     * Send a Slack webhook notification.
     */
    public function send(NotificationLog $log, string $subject, string $content): void
    {
        $webhookUrl = $log->recipient;

        if (empty($webhookUrl)) {
            throw new \Exception('No Slack webhook URL provided');
        }

        // Build the Slack message payload
        $payload = [
            'text' => $subject ?: 'Notification',
            'blocks' => [
                [
                    'type' => 'header',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $subject ?: 'Notification',
                    ],
                ],
                [
                    'type' => 'section',
                    'text' => [
                        'type' => 'mrkdwn',
                        'text' => $content,
                    ],
                ],
            ],
        ];

        // Send to Slack webhook
        $response = Http::post($webhookUrl, $payload);

        if (!$response->successful()) {
            throw new \Exception('Slack webhook failed: ' . $response->body());
        }

        Log::channel('single')->info("Slack notification sent", [
            'webhook' => substr($webhookUrl, 0, 50) . '...',
            'log_id' => $log->id,
        ]);
    }
}
