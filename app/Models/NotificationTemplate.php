<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class NotificationTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'name',
        'channel',
        'subject',
        'body',
    ];

    /**
     * Get the user that owns the template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the notification rules using this template.
     */
    public function notificationRules(): HasMany
    {
        return $this->hasMany(NotificationRule::class);
    }

    /**
     * Render the template body with the given payload.
     * Replaces {{variable}} placeholders with payload values.
     */
    public function renderBody(array $payload): string
    {
        return $this->interpolate($this->body, $payload);
    }

    /**
     * Render the template subject with the given payload.
     */
    public function renderSubject(array $payload): string
    {
        return $this->interpolate($this->subject ?? '', $payload);
    }

    /**
     * Interpolate variables in the content.
     * Supports dot notation: {{user.name}}
     */
    protected function interpolate(string $content, array $payload): string
    {
        return preg_replace_callback('/\{\{(\s*[\w.]+\s*)\}\}/', function ($matches) use ($payload) {
            $key = trim($matches[1]);
            return Arr::get($payload, $key, $matches[0]);
        }, $content);
    }
}
