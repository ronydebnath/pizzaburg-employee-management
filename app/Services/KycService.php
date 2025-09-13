<?php

namespace App\Services;

use App\Models\KycVerification;
use App\Models\OnboardingInvite;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KycService
{
    /**
     * Create a new KYC verification session
     */
    public function createVerification(OnboardingInvite $invite, string $type = 'selfie_liveness', string $provider = 'internal'): KycVerification
    {
        return KycVerification::create([
            'onboarding_invite_id' => $invite->id,
            'provider' => $provider,
            'type' => $type,
            'status' => 'pending',
            'expires_at' => now()->addHours(24), // 24 hours to complete
        ]);
    }

    /**
     * Process selfie liveness verification
     */
    public function processSelfieLiveness(KycVerification $verification, string $selfieData): array
    {
        try {
            $verification->markAsProcessing();

            // Save selfie image
            $selfiePath = $this->saveSelfieImage($verification, $selfieData);
            $verification->update(['selfie_image_path' => $selfiePath]);

            // Perform liveness detection (simplified version)
            $livenessResult = $this->performLivenessDetection($selfieData);

            if ($livenessResult['is_live']) {
                $verification->markAsApproved($livenessResult);
                
                Log::info('KYC verification approved', [
                    'verification_id' => $verification->verification_id,
                    'invite_id' => $verification->onboarding_invite_id,
                    'liveness_score' => $livenessResult['liveness_score'],
                ]);

                return [
                    'success' => true,
                    'status' => 'approved',
                    'message' => 'Selfie liveness verification successful',
                    'verification_id' => $verification->verification_id,
                    'result' => $livenessResult,
                ];
            } else {
                $verification->markAsRejected('Liveness detection failed', $livenessResult);
                
                Log::warning('KYC verification rejected - liveness failed', [
                    'verification_id' => $verification->verification_id,
                    'invite_id' => $verification->onboarding_invite_id,
                    'liveness_score' => $livenessResult['liveness_score'],
                ]);

                return [
                    'success' => false,
                    'status' => 'rejected',
                    'message' => 'Liveness detection failed. Please ensure you are a real person and try again.',
                    'verification_id' => $verification->verification_id,
                    'result' => $livenessResult,
                ];
            }

        } catch (\Exception $e) {
            $verification->markAsFailed('Processing error: ' . $e->getMessage());
            
            Log::error('KYC verification failed', [
                'verification_id' => $verification->verification_id,
                'invite_id' => $verification->onboarding_invite_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'An error occurred during verification. Please try again.',
                'verification_id' => $verification->verification_id,
            ];
        }
    }

    /**
     * Get verification status
     */
    public function getVerificationStatus(string $verificationId): ?array
    {
        $verification = KycVerification::where('verification_id', $verificationId)->first();

        if (!$verification) {
            return null;
        }

        return [
            'verification_id' => $verification->verification_id,
            'status' => $verification->status,
            'type' => $verification->type,
            'provider' => $verification->provider,
            'created_at' => $verification->created_at,
            'verified_at' => $verification->verified_at,
            'expires_at' => $verification->expires_at,
            'rejection_reason' => $verification->rejection_reason,
            'result_data' => $verification->result_data,
        ];
    }

    /**
     * Get verification by onboarding invite
     */
    public function getVerificationByInvite(OnboardingInvite $invite, string $type = 'selfie_liveness'): ?KycVerification
    {
        return KycVerification::where('onboarding_invite_id', $invite->id)
            ->where('type', $type)
            ->latest()
            ->first();
    }

    /**
     * Check if verification is required for invite
     */
    public function isVerificationRequired(OnboardingInvite $invite): bool
    {
        // Check if there's already an approved verification
        $approvedVerification = KycVerification::where('onboarding_invite_id', $invite->id)
            ->where('status', 'approved')
            ->first();

        return !$approvedVerification;
    }

    /**
     * Save selfie image from base64 data
     */
    private function saveSelfieImage(KycVerification $verification, string $selfieData): string
    {
        // Remove data URL prefix if present
        if (strpos($selfieData, 'data:image') === 0) {
            $selfieData = substr($selfieData, strpos($selfieData, ',') + 1);
        }

        // Decode base64 data
        $imageData = base64_decode($selfieData);

        if ($imageData === false) {
            throw new \Exception('Invalid selfie image data');
        }

        // Generate filename
        $filename = 'kyc/selfies/' . $verification->verification_id . '_selfie.jpg';

        // Save to storage
        Storage::put($filename, $imageData);

        return $filename;
    }

    /**
     * Perform liveness detection (simplified implementation)
     * In a real implementation, this would use AI/ML services like:
     * - AWS Rekognition
     * - Google Cloud Vision
     * - Azure Face API
     * - Third-party services like Sumsub, Onfido, etc.
     */
    private function performLivenessDetection(string $selfieData): array
    {
        // This is a simplified implementation
        // In production, you would integrate with actual liveness detection services
        
        // For demo purposes, we'll simulate liveness detection
        $livenessScore = $this->simulateLivenessDetection($selfieData);
        
        return [
            'is_live' => $livenessScore > 0.7, // Threshold for liveness
            'liveness_score' => $livenessScore,
            'detection_method' => 'simulated',
            'confidence' => $livenessScore,
            'timestamp' => now()->toISOString(),
            'metadata' => [
                'image_size' => strlen(base64_decode($selfieData)),
                'detection_version' => '1.0.0',
            ],
        ];
    }

    /**
     * Simulate liveness detection (for demo purposes)
     * In production, replace this with actual AI/ML service calls
     */
    private function simulateLivenessDetection(string $selfieData): float
    {
        // Simulate some randomness in liveness detection
        // In reality, this would be replaced with actual AI analysis
        
        $imageSize = strlen(base64_decode($selfieData));
        
        // Simulate different scenarios
        $scenarios = [
            'good_selfie' => 0.85, // Good lighting, clear face
            'poor_lighting' => 0.45, // Poor lighting
            'blurry_image' => 0.30, // Blurry or low quality
            'no_face' => 0.10, // No face detected
            'multiple_faces' => 0.60, // Multiple faces detected
        ];

        // Simple heuristic based on image size and some randomness
        if ($imageSize > 50000) { // Large image
            $baseScore = $scenarios['good_selfie'];
        } elseif ($imageSize > 20000) { // Medium image
            $baseScore = $scenarios['poor_lighting'];
        } else { // Small image
            $baseScore = $scenarios['blurry_image'];
        }

        // Add some randomness to simulate real detection
        $randomness = (mt_rand(0, 100) - 50) / 1000; // ±5% variation
        
        return max(0, min(1, $baseScore + $randomness));
    }

    /**
     * Clean up expired verifications
     */
    public function cleanupExpiredVerifications(): int
    {
        $expiredVerifications = KycVerification::where('expires_at', '<', now())
            ->where('status', 'pending')
            ->get();

        $count = 0;
        foreach ($expiredVerifications as $verification) {
            $verification->markAsFailed('Verification expired');
            $verification->deleteSelfieFile();
            $verification->deleteDocumentFile();
            $count++;
        }

        Log::info('Cleaned up expired KYC verifications', ['count' => $count]);

        return $count;
    }
}
