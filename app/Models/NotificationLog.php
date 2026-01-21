<?php

namespace App\Models;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'event_source_id',
        'notification_rule_id',
        'channel',
        'payload',
        'recipient',
        'status',
        'error_message',
        'attempts',
        'sent_at',
    ];

    protected $attributes = [
        'attempts' => 0,
        'status' => NotificationStatus::Pending->value,
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'sent_at' => 'datetime',
            'status' => NotificationStatus::class,
        ];
    }

    /**
     * Event source this log belongs to.
     */
    public function eventSource(): BelongsTo
    {
        return $this->belongsTo(EventSource::class);
    }

    /**
     * Notification rule this log belongs to.
     */
    public function notificationRule(): BelongsTo
    {
        return $this->belongsTo(NotificationRule::class);
    }

    /**
     * Mark the notification as processing.
     */
    public function markAsProcessing(): void
    {
        $this->increment('attempts');
        $this->forceFill(['status' => NotificationStatus::Processing])->save();
    }

    /**
     * Mark the notification as sent.
     */
    public function markAsSent(): void
    {
        $this->forceFill([
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
            'error_message' => null,
        ])->save();
    }

    /**
     * Mark the notification as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->forceFill([
            'status' => NotificationStatus::Failed,
            'error_message' => $errorMessage,
        ])->save();
    }

    /**
     * Reset to pending for retry.
     */
    public function resetToPending(): void
    {
        $this->forceFill(['status' => NotificationStatus::Pending])->save();
    }

    /**
     * Scope a query to only include pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', NotificationStatus::Pending);
    }

    /**
     * Scope a query to only include failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', NotificationStatus::Failed);
    }

    /**
     * Check if the notification can be retried.
     */
    public function canRetry(int $maxAttempts = 3): bool
    {
        return $this->status === NotificationStatus::Failed
            && ($this->attempts ?? 0) < $maxAttempts;
    }
}
