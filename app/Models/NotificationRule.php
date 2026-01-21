<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class NotificationRule extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'event_source_id',
        'notification_template_id',
        'channel',
        'conditions',
        'recipient_email',
        'recipient_phone',
        'recipient_slack_webhook',
        'recipient_field',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active' => 'boolean',
            'channel' => NotificationChannel::class,
        ];
    }

    /**
     * Get the event source this rule belongs to.
     */
    public function eventSource(): BelongsTo
    {
        return $this->belongsTo(EventSource::class);
    }

    /**
     * Get the notification template for this rule.
     */
    public function notificationTemplate(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class);
    }

    /**
     * Get the user that owns this rule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification logs for this rule.
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Check if the payload matches the rule conditions.
     */
    public function matchesConditions(array $payload): bool
    {
        if (empty($this->conditions)) {
            return true;
        }

        foreach ($this->conditions as $field => $expectedValue) {
            $actualValue = Arr::get($payload, $field);
            
            if ($actualValue !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the recipient based on channel and payload.
     */
    public function getRecipient(array $payload): ?string
    {
        // First check if recipient should be extracted from payload
        if ($this->recipient_field) {
            $recipient = Arr::get($payload, $this->recipient_field);
            if ($recipient) {
                return $recipient;
            }
        }

        // Fall back to static recipient based on channel
        return match ($this->channel) {
            NotificationChannel::Email => $this->recipient_email,
            NotificationChannel::Sms => $this->recipient_phone,
            NotificationChannel::Slack => $this->recipient_slack_webhook,
            default => null,
        };
    }

    /**
     * Scope a query to only include active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
