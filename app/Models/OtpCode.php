<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OtpCode extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'email',
        'code',
        'type', // 'login', 'verification', 'recovery'
        'purpose', // 'onboarding', 'login', 'password_reset'
        'expires_at',
        'used_at',
        'attempts',
        'max_attempts',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = $model->generateCode();
            }
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addMinutes(10); // 10 minutes expiry
            }
            if (empty($model->max_attempts)) {
                $model->max_attempts = 3;
            }
            if (empty($model->attempts)) {
                $model->attempts = 0;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generateCode(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function isExhausted(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    public function canBeUsed(): bool
    {
        return !$this->isExpired() && !$this->isUsed() && !$this->isExhausted();
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function getMaskedPhoneAttribute(): string
    {
        if (!$this->phone) return '';
        
        $phone = $this->phone;
        if (strlen($phone) > 4) {
            return substr($phone, 0, 2) . str_repeat('*', strlen($phone) - 4) . substr($phone, -2);
        }
        return $phone;
    }

    public function getMaskedEmailAttribute(): string
    {
        if (!$this->email) return '';
        
        $email = $this->email;
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $username = $parts[0];
            $domain = $parts[1];
            
            if (strlen($username) > 2) {
                $maskedUsername = substr($username, 0, 1) . str_repeat('*', strlen($username) - 2) . substr($username, -1);
            } else {
                $maskedUsername = str_repeat('*', strlen($username));
            }
            
            return $maskedUsername . '@' . $domain;
        }
        
        return $email;
    }
}