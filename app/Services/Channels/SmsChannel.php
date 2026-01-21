<?php

namespace App\Services\Channels;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class SmsChannel implements NotificationChannelInterface
{
    /**
     * Send an SMS notification.
     * 
     * This is a stub implementation that logs the SMS.
     * In production, integrate with Twilio, Vonage, or similar.
     */
    public function send(NotificationLog $log, string $subject, string $content): void
    {
        $recipient = $log->recipient;

        if (empty($recipient)) {
            throw new \Exception('No recipient phone number provided');
        }

        // For demo: Log the SMS instead of actually sending
        // In production, integrate with Twilio/Vonage here
        Log::channel('single')->info("SMS notification sent (simulated)", [
            'to' => $recipient,
            'message' => $content,
            'log_id' => $log->id,
        ]);

        // Example Twilio integration (commented out):
        // $twilio = new \Twilio\Rest\Client(
        //     config('services.twilio.sid'),
        //     config('services.twilio.token')
        // );
        // $twilio->messages->create($recipient, [
        //     'from' => config('services.twilio.from'),
        //     'body' => $content,
        // ]);
    }
}
