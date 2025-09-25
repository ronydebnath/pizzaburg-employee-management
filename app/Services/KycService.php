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
     * Process profile information submission
     */
    public function processProfileSubmission(KycVerification $verification, array $data): array
    {
        try {
            $verification->markAsProcessing();

            // Save profile information
            $profileData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'national_id' => $data['national_id'],
                'address' => $data['address'],
                'emergency_contact_name' => $data['emergency_contact_name'],
                'emergency_contact_phone' => $data['emergency_contact_phone'],
            ];

            // Handle profile photo upload
            if (isset($data['profile_photo']) && $data['profile_photo']) {
                $profileImagePath = $this->saveProfileImage($verification, $data['profile_photo']);
                $profileData['profile_image_path'] = $profileImagePath;
            }

            // Handle national ID document upload
            if (isset($data['national_id_photo']) && $data['national_id_photo']) {
                $documentPath = $this->saveDocumentImage($verification, $data['national_id_photo']);
                $profileData['document_image_path'] = $documentPath;
            }

            // Update verification with profile data
            $verification->update($profileData);

            // Mark as pending HR review (not auto-approved)
            $verification->update([
                'status' => 'pending_hr_review',
                'verification_data' => [
                    'submitted_at' => now()->toISOString(),
                    'submission_method' => 'web_form',
                ],
            ]);
            
            Log::info('KYC profile information submitted', [
                'verification_id' => $verification->verification_id,
                'invite_id' => $verification->onboarding_invite_id,
                'employee_name' => $verification->full_name,
            ]);

            return [
                'success' => true,
                'status' => 'pending_hr_review',
                'message' => 'Profile information submitted successfully. HR will review and approve your account.',
                'verification_id' => $verification->verification_id,
            ];

        } catch (\Exception $e) {
            $verification->markAsFailed('Processing error: ' . $e->getMessage());
            
            Log::error('KYC profile submission failed', [
                'verification_id' => $verification->verification_id,
                'invite_id' => $verification->onboarding_invite_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'An error occurred during submission. Please try again.',
                'verification_id' => $verification->verification_id,
            ];
        }
    }

    /**
     * Process selfie liveness verification (legacy method - kept for compatibility)
     */
    public function processSelfieLiveness(KycVerification $verification, string $selfieData): array
    {
        return $this->processProfileSubmission($verification, ['selfie' => $selfieData]);
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
     * Save profile image from uploaded file
     */
    private function saveProfileImage(KycVerification $verification, $file): string
    {
        // Generate filename
        $filename = 'kyc/profiles/' . $verification->verification_id . '_profile.' . $file->getClientOriginalExtension();

        // Save to storage
        $path = $file->storeAs('kyc/profiles', $verification->verification_id . '_profile.' . $file->getClientOriginalExtension());

        return $path;
    }

    /**
     * Save national ID document image from uploaded file
     */
    private function saveDocumentImage(KycVerification $verification, $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $verification->verification_id . '_national_id.' . $extension;

        return $file->storeAs('kyc/documents', $filename);
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
