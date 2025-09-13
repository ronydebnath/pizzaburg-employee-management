<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'device_fingerprint',
        'device_name',
        'device_type', // 'mobile', 'tablet', 'desktop', 'unknown'
        'os_name',
        'os_version',
        'browser_name',
        'browser_version',
        'ip_address',
        'user_agent',
        'is_trusted',
        'last_seen_at',
        'first_seen_at',
    ];

    protected $casts = [
        'is_trusted' => 'boolean',
        'last_seen_at' => 'datetime',
        'first_seen_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->device_fingerprint)) {
                $model->device_fingerprint = $model->generateFingerprint();
            }
            if (empty($model->first_seen_at)) {
                $model->first_seen_at = now();
            }
            if (empty($model->last_seen_at)) {
                $model->last_seen_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generateFingerprint(): string
    {
        $components = [
            $this->user_agent ?? '',
            $this->ip_address ?? '',
            $this->os_name ?? '',
            $this->browser_name ?? '',
        ];
        
        return hash('sha256', implode('|', $components));
    }

    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    public function markAsTrusted(): void
    {
        $this->update(['is_trusted' => true]);
    }

    public function markAsUntrusted(): void
    {
        $this->update(['is_trusted' => false]);
    }

    public function isTrusted(): bool
    {
        return $this->is_trusted;
    }

    public function isRecentlyActive(int $minutes = 30): bool
    {
        return $this->last_seen_at && $this->last_seen_at->isAfter(now()->subMinutes($minutes));
    }

    public function getDeviceDescriptionAttribute(): string
    {
        $parts = [];
        
        if ($this->device_name) {
            $parts[] = $this->device_name;
        }
        
        if ($this->os_name) {
            $os = $this->os_name;
            if ($this->os_version) {
                $os .= ' ' . $this->os_version;
            }
            $parts[] = $os;
        }
        
        if ($this->browser_name) {
            $browser = $this->browser_name;
            if ($this->browser_version) {
                $browser .= ' ' . $this->browser_version;
            }
            $parts[] = $browser;
        }
        
        return implode(' - ', $parts) ?: 'Unknown Device';
    }

    public function getLocationAttribute(): string
    {
        // TODO: Implement IP geolocation service
        return $this->ip_address ?: 'Unknown Location';
    }
}