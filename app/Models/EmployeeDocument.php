<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'onboarding_invite_id',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'notes',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function onboardingInvite(): BelongsTo
    {
        return $this->belongsTo(OnboardingInvite::class);
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isUploaded(): bool
    {
        return $this->status === 'uploaded';
    }

    public function markAsVerified(string $notes = null): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function markAsRejected(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason,
        ]);
    }

    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            return Storage::delete($this->file_path);
        }
        return false;
    }
}
