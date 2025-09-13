<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KycVerification extends Model
{
    protected $fillable = [
        'onboarding_invite_id',
        'verification_id',
        'provider',
        'status',
        'type',
        'verification_data',
        'result_data',
        'selfie_image_path',
        'document_image_path',
        'rejection_reason',
        'verified_at',
        'expires_at',
        // Profile information fields
        'first_name',
        'last_name',
        'date_of_birth',
        'national_id',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'profile_image_path',
    ];

    protected $casts = [
        'verification_data' => 'array',
        'result_data' => 'array',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->verification_id)) {
                $model->verification_id = 'KYC-' . strtoupper(Str::random(12));
            }
        });
    }

    public function onboardingInvite(): BelongsTo
    {
        return $this->belongsTo(OnboardingInvite::class);
    }

    public function getSelfieUrlAttribute(): ?string
    {
        return $this->selfie_image_path ? Storage::url($this->selfie_image_path) : null;
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document_image_path ? Storage::url($this->document_image_path) : null;
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image_path ? Storage::url($this->profile_image_path) : null;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsApproved(array $resultData = []): void
    {
        $this->update([
            'status' => 'approved',
            'result_data' => $resultData,
            'verified_at' => now(),
        ]);
    }

    public function markAsRejected(string $reason, array $resultData = []): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'result_data' => $resultData,
            'verified_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason, array $resultData = []): void
    {
        $this->update([
            'status' => 'failed',
            'rejection_reason' => $reason,
            'result_data' => $resultData,
            'verified_at' => now(),
        ]);
    }

    public function deleteSelfieFile(): bool
    {
        if ($this->selfie_image_path && Storage::exists($this->selfie_image_path)) {
            return Storage::delete($this->selfie_image_path);
        }
        return false;
    }

    public function deleteDocumentFile(): bool
    {
        if ($this->document_image_path && Storage::exists($this->document_image_path)) {
            return Storage::delete($this->document_image_path);
        }
        return false;
    }
}
