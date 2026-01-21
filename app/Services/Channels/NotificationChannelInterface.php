<?php

namespace App\Services\Channels;

use App\Models\NotificationLog;

interface NotificationChannelInterface
{
    /**
     * Send a notification.
     *
     * @param NotificationLog $log The notification log entry
     * @param string $subject The rendered subject (for email)
     * @param string $content The rendered message content
     * @return void
     * @throws \Exception If sending fails
     */
    public function send(NotificationLog $log, string $subject, string $content): void;
}
