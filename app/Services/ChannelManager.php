<?php

namespace App\Services;

use App\Services\Channels\EmailChannel;
use App\Services\Channels\NotificationChannelInterface;
use App\Services\Channels\SlackChannel;
use App\Services\Channels\SmsChannel;
use InvalidArgumentException;

class ChannelManager
{
    /**
     * The registered channel drivers.
     */
    protected array $drivers = [];

    /**
     * Get a channel driver instance.
     */
    public function driver(string $channel): NotificationChannelInterface
    {
        if (!isset($this->drivers[$channel])) {
            $this->drivers[$channel] = $this->createDriver($channel);
        }

        return $this->drivers[$channel];
    }

    /**
     * Create a new driver instance.
     */
    protected function createDriver(string $channel): NotificationChannelInterface
    {
        return match ($channel) {
            'email' => new EmailChannel(),
            'sms' => new SmsChannel(),
            'slack' => new SlackChannel(),
            default => throw new InvalidArgumentException("Unsupported channel: {$channel}"),
        };
    }

    /**
     * Get all supported channels.
     */
    public function supportedChannels(): array
    {
        return ['email', 'sms', 'slack'];
    }
}
