<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     */
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

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the event source this log belongs to.
     */
    public function eventSource(): BelongsTo
    {
        return $this->belongsTo(EventSource::class);
    }

    /**
     * Get the notification rule this log belongs to.
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
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'attempts' => $this->attempts + 1,
        ]);
    }

    /**
     * Mark the notification as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark the notification as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Reset to pending for retry.
     */
    public function resetToPending(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Scope a query to only include pending notifications.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include failed notifications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if the notification can be retried.
     */
    public function canRetry(int $maxAttempts = 3): bool
    {
        return $this->attempts < $maxAttempts 
            && $this->status === self::STATUS_FAILED;
    }
}
