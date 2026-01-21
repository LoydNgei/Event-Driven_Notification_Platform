<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSource extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'schema',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the event source.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification rules for this event source.
     */
    public function notificationRules(): HasMany
    {
        return $this->hasMany(NotificationRule::class);
    }

    /**
     * Get the notification logs for this event source.
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Get only active notification rules.
     */
    public function activeRules(): HasMany
    {
        return $this->notificationRules()->where('is_active', true);
    }

    /**
     * Scope a query to only include active event sources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find an event source by name.
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }
}
