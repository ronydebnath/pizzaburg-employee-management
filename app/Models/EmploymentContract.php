<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmploymentContract extends Model
{
    protected $fillable = [
        'onboarding_invite_id',
        'contract_number',
        'template_key',
        'status',
        'contract_data',
        'signature_file_path',
        'signed_pdf_path',
        'sent_at',
        'signed_at',
        'completed_at',
    ];

    protected $casts = [
        'contract_data' => 'array',
        'sent_at' => 'datetime',
        'signed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->contract_number)) {
                $model->contract_number = 'CONTRACT-' . strtoupper(Str::random(8));
            }
        });
    }

    public function onboardingInvite(): BelongsTo
    {
        return $this->belongsTo(OnboardingInvite::class);
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature_file_path ? Storage::url($this->signature_file_path) : null;
    }

    public function getSignedPdfUrlAttribute(): ?string
    {
        return $this->signed_pdf_path ? Storage::url($this->signed_pdf_path) : null;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsSigned(): void
    {
        $this->update([
            'status' => 'signed',
            'signed_at' => now(),
        ]);
    }

    public function markAsCompleted(string $signedPdfPath): void
    {
        $this->update([
            'status' => 'completed',
            'signed_pdf_path' => $signedPdfPath,
            'completed_at' => now(),
        ]);
    }

    public function deleteSignatureFile(): bool
    {
        if ($this->signature_file_path && Storage::exists($this->signature_file_path)) {
            return Storage::delete($this->signature_file_path);
        }
        return false;
    }

    public function deleteSignedPdf(): bool
    {
        if ($this->signed_pdf_path && Storage::exists($this->signed_pdf_path)) {
            return Storage::delete($this->signed_pdf_path);
        }
        return false;
    }
}
