<?php

namespace App\Http\Controllers;

use App\Models\KycVerification;
use App\Models\OnboardingInvite;
use App\Services\KycService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KycController extends Controller
{
    protected KycService $kycService;

    public function __construct(KycService $kycService)
    {
        $this->kycService = $kycService;
    }

    /**
     * Display KYC verification page
     */
    public function show(string $token)
    {
        try {
            // Find the onboarding invite by token
            $invite = OnboardingInvite::where('token', $token)
                ->where('status', 'sent')
                ->where('expires_at', '>', now())
                ->first();

            if (!$invite) {
                return view('kyc.expired', [
                    'message' => 'This invitation link has expired or is invalid.'
                ]);
            }

            // Check if verification is required
            if (!$this->kycService->isVerificationRequired($invite)) {
                return view('kyc.already-verified', [
                    'invite' => $invite,
                    'message' => 'Your identity has already been verified.'
                ]);
            }

            // Get or create KYC verification
            $verification = $this->kycService->getVerificationByInvite($invite);
            
            if (!$verification) {
                $verification = $this->kycService->createVerification($invite);
            }

            // Check if verification is expired
            if ($verification->isExpired()) {
                return view('kyc.expired', [
                    'message' => 'Your verification session has expired. Please request a new invitation.'
                ]);
            }

            return view('kyc.verify', [
                'verification' => $verification,
                'invite' => $invite,
            ]);

        } catch (\Exception $e) {
            Log::error('KYC display error: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getTraceAsString(),
            ]);

            return view('kyc.error', [
                'message' => 'An error occurred while loading the verification page. Please try again later.'
            ]);
        }
    }

    /**
     * Process KYC profile information submission
     */
    public function verifySelfie(Request $request, string $token): JsonResponse
    {
        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'national_id' => 'required|string|max:255',
                'address' => 'required|string',
                'emergency_contact_name' => 'required|string|max:255',
                'emergency_contact_phone' => 'required|string|max:20',
                'verification_id' => 'required|string',
                'profile_photo' => 'nullable|image|max:2048',
                'national_id_photo' => 'nullable|image|max:4096',
            ]);

            // Find the onboarding invite
            $invite = OnboardingInvite::where('token', $token)
                ->where('status', 'sent')
                ->where('expires_at', '>', now())
                ->first();

            if (!$invite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired invitation link.'
                ], 400);
            }

            // Find the verification
            $verification = KycVerification::where('verification_id', $request->verification_id)
                ->where('onboarding_invite_id', $invite->id)
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification session not found.'
                ], 404);
            }

            if ($verification->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification session has expired.'
                ], 400);
            }

            if (!$verification->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification has already been processed.'
                ], 400);
            }

            // Process the profile information submission
            $result = $this->kycService->processProfileSubmission($verification, $request->all());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('KYC profile submission error: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during submission. Please try again.'
            ], 500);
        }
    }

    /**
     * Get verification status
     */
    public function status(string $verificationId): JsonResponse
    {
        try {
            $status = $this->kycService->getVerificationStatus($verificationId);

            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'verification' => $status,
            ]);

        } catch (\Exception $e) {
            Log::error('KYC status check error: ' . $e->getMessage(), [
                'verification_id' => $verificationId,
                'error' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking verification status.'
            ], 500);
        }
    }

    /**
     * Retry verification
     */
    public function retry(string $token): JsonResponse
    {
        try {
            // Find the onboarding invite
            $invite = OnboardingInvite::where('token', $token)
                ->where('status', 'sent')
                ->where('expires_at', '>', now())
                ->first();

            if (!$invite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired invitation link.'
                ], 400);
            }

            // Create a new verification session
            $verification = $this->kycService->createVerification($invite);

            return response()->json([
                'success' => true,
                'message' => 'New verification session created.',
                'verification_id' => $verification->verification_id,
            ]);

        } catch (\Exception $e) {
            Log::error('KYC retry error: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating a new verification session.'
            ], 500);
        }
    }
}
