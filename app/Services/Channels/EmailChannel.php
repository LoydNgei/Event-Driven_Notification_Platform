<?php

namespace App\Services\Channels;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailChannel implements NotificationChannelInterface
{
    /**
     * Send an email notification.
     */
    public function send(NotificationLog $log, string $subject, string $content): void
    {
        $recipient = $log->recipient;

        if (empty($recipient)) {
            throw new \Exception('No recipient email address provided');
        }

        // For demo purposes, we'll use Laravel's basic mail functionality
        // In production, this would use Mailgun, SES, etc.
        Mail::raw($content, function ($message) use ($recipient, $subject) {
            $message->to($recipient)
                ->subject($subject);
        });

        Log::channel('single')->info("Email notification sent", [
            'to' => $recipient,
            'subject' => $subject,
            'log_id' => $log->id,
        ]);
    }
}
