<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingStep extends Model
{
    protected $fillable = [
        'onboarding_invite_id',
        'step_name',
        'status',
        'step_data',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'step_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function onboardingInvite(): BelongsTo
    {
        return $this->belongsTo(OnboardingInvite::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $data = []): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'step_data' => array_merge($this->step_data ?? [], $data),
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }
}
