<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OnboardingInvite extends Model
{
    protected $fillable = [
        'branch_id',
        'position_id',
        'email',
        'phone',
        'first_name',
        'last_name',
        'token',
        'status',
        'expires_at',
        'sent_at',
        'completed_at',
        'invitation_data',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'invitation_data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(64);
            }
            if (empty($model->expires_at)) {
                $model->expires_at = now()->addDays(7); // 7 days expiry
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(OnboardingStep::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function contract(): HasMany
    {
        return $this->hasMany(EmploymentContract::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInviteUrlAttribute(): string
    {
        return route('onboarding.invite', ['token' => $this->token]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeAccessed(): bool
    {
        return !$this->isExpired() && !$this->isCompleted();
    }
}
