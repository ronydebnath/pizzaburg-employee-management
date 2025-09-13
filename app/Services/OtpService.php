<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use App\Models\Device;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class OtpService
{
    /**
     * Send OTP to phone or email
     */
    public function sendOtp(string $phone = null, string $email = null, string $type = 'login', string $purpose = 'onboarding', Request $request = null): array
    {
        try {
            // Rate limiting check
            $identifier = $phone ?: $email;
            $rateLimitKey = "otp_send:{$identifier}";
            
            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                $seconds = RateLimiter::availableIn($rateLimitKey);
                return [
                    'success' => false,
                    'message' => "Too many OTP requests. Please try again in {$seconds} seconds.",
                    'retry_after' => $seconds,
                ];
            }

            // Check for existing valid OTP
            $existingOtp = $this->getValidOtp($phone, $email, $type, $purpose);
            if ($existingOtp) {
                return [
                    'success' => false,
                    'message' => 'An OTP has already been sent. Please wait before requesting another.',
                    'retry_after' => $existingOtp->expires_at->diffInSeconds(now()),
                ];
            }

            // Create new OTP
            $otp = OtpCode::create([
                'phone' => $phone,
                'email' => $email,
                'type' => $type,
                'purpose' => $purpose,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);

            // Send OTP via SMS or Email
            $sent = $this->deliverOtp($otp);

            if ($sent) {
                // Record rate limit attempt
                RateLimiter::hit($rateLimitKey, 300); // 5 minutes window

                Log::info('OTP sent successfully', [
                    'otp_id' => $otp->id,
                    'phone' => $otp->masked_phone,
                    'email' => $otp->masked_email,
                    'type' => $type,
                    'purpose' => $purpose,
                ]);

                return [
                    'success' => true,
                    'message' => 'OTP sent successfully',
                    'expires_in' => $otp->expires_at->diffInSeconds(now()),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send OTP', [
                'phone' => $phone,
                'email' => $email,
                'type' => $type,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while sending OTP. Please try again.',
            ];
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $code, string $phone = null, string $email = null, string $type = 'login', string $purpose = 'onboarding', Request $request = null): array
    {
        try {
            $otp = $this->getValidOtp($phone, $email, $type, $purpose);

            if (!$otp) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.',
                ];
            }

            // Check if OTP is exhausted
            if ($otp->isExhausted()) {
                Log::warning('OTP verification failed - exhausted attempts', [
                    'otp_id' => $otp->id,
                    'attempts' => $otp->attempts,
                    'max_attempts' => $otp->max_attempts,
                ]);

                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new OTP.',
                ];
            }

            // Increment attempts
            $otp->incrementAttempts();

            // Verify code
            if ($otp->code !== $code) {
                Log::warning('OTP verification failed - invalid code', [
                    'otp_id' => $otp->id,
                    'attempts' => $otp->attempts,
                ]);

                return [
                    'success' => false,
                    'message' => 'Invalid OTP code. Please try again.',
                    'attempts_remaining' => $otp->max_attempts - $otp->attempts,
                ];
            }

            // Mark OTP as used
            $otp->markAsUsed();

            // Find or create user
            $user = $this->findOrCreateUser($phone, $email);

            // Register device if request is provided
            $device = null;
            if ($request) {
                $device = $this->registerDevice($user, $request);
            }

            Log::info('OTP verified successfully', [
                'otp_id' => $otp->id,
                'user_id' => $user->id,
                'device_id' => $device?->id,
            ]);

            return [
                'success' => true,
                'message' => 'OTP verified successfully',
                'user' => $user,
                'device' => $device,
                'access_token' => $this->generateAccessToken($user),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to verify OTP', [
                'phone' => $phone,
                'email' => $email,
                'type' => $type,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while verifying OTP. Please try again.',
            ];
        }
    }

    /**
     * Get valid OTP for verification
     */
    private function getValidOtp(string $phone = null, string $email = null, string $type = 'login', string $purpose = 'onboarding'): ?OtpCode
    {
        $query = OtpCode::where('type', $type)
                       ->where('purpose', $purpose)
                       ->where('expires_at', '>', now())
                       ->whereNull('used_at');

        if ($phone) {
            $query->where('phone', $phone);
        }

        if ($email) {
            $query->where('email', $email);
        }

        return $query->orderBy('created_at', 'desc')->first();
    }

    /**
     * Deliver OTP via SMS or Email
     */
    private function deliverOtp(OtpCode $otp): bool
    {
        try {
            if ($otp->phone) {
                return $this->sendSmsOtp($otp);
            }

            if ($otp->email) {
                return $this->sendEmailOtp($otp);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to deliver OTP', [
                'otp_id' => $otp->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send OTP via SMS
     */
    private function sendSmsOtp(OtpCode $otp): bool
    {
        try {
            // TODO: Integrate with SMS service like Twilio, AWS SNS, etc.
            $message = "Your Pizzaburg verification code is: {$otp->code}. Valid for 10 minutes.";
            
            // For now, just log the SMS
            Log::info('SMS OTP would be sent', [
                'to' => $otp->masked_phone,
                'message' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS OTP', [
                'otp_id' => $otp->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send OTP via Email
     */
    private function sendEmailOtp(OtpCode $otp): bool
    {
        try {
            // TODO: Create proper email template and send via mail service
            $subject = 'Your Pizzaburg Verification Code';
            $message = "Your verification code is: {$otp->code}. Valid for 10 minutes.";
            
            // For now, just log the email
            Log::info('Email OTP would be sent', [
                'to' => $otp->masked_email,
                'subject' => $subject,
                'message' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email OTP', [
                'otp_id' => $otp->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Find or create user
     */
    private function findOrCreateUser(string $phone = null, string $email = null): User
    {
        $user = null;

        if ($phone) {
            $user = User::where('phone', $phone)->first();
        }

        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }

        if (!$user) {
            // Create new user for onboarding
            $user = User::create([
                'name' => 'New Employee', // Will be updated during onboarding
                'email' => $email,
                'phone' => $phone,
                'role' => 'employee',
                'status' => 'pending_onboarding',
            ]);
        }

        return $user;
    }

    /**
     * Register device
     */
    private function registerDevice(User $user, Request $request): Device
    {
        $fingerprint = $this->generateDeviceFingerprint($request);
        
        $device = Device::where('device_fingerprint', $fingerprint)->first();

        if (!$device) {
            $device = Device::create([
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
                'device_name' => $this->extractDeviceName($request),
                'device_type' => $this->extractDeviceType($request),
                'os_name' => $this->extractOsName($request),
                'os_version' => $this->extractOsVersion($request),
                'browser_name' => $this->extractBrowserName($request),
                'browser_version' => $this->extractBrowserVersion($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } else {
            $device->updateLastSeen();
        }

        return $device;
    }

    /**
     * Generate device fingerprint
     */
    private function generateDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->ip(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', array_filter($components)));
    }

    /**
     * Extract device information from request
     */
    private function extractDeviceName(Request $request): ?string
    {
        // TODO: Implement device name extraction from user agent
        return null;
    }

    private function extractDeviceType(Request $request): string
    {
        $userAgent = strtolower($request->userAgent());
        
        if (strpos($userAgent, 'mobile') !== false) {
            return 'mobile';
        }
        
        if (strpos($userAgent, 'tablet') !== false) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    private function extractOsName(Request $request): ?string
    {
        // TODO: Implement OS extraction from user agent
        return null;
    }

    private function extractOsVersion(Request $request): ?string
    {
        // TODO: Implement OS version extraction from user agent
        return null;
    }

    private function extractBrowserName(Request $request): ?string
    {
        // TODO: Implement browser extraction from user agent
        return null;
    }

    private function extractBrowserVersion(Request $request): ?string
    {
        // TODO: Implement browser version extraction from user agent
        return null;
    }

    /**
     * Generate access token (placeholder for JWT/OAuth2)
     */
    private function generateAccessToken(User $user): string
    {
        // TODO: Implement JWT token generation
        return 'access_token_' . $user->id . '_' . time();
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpiredOtps(): int
    {
        return OtpCode::where('expires_at', '<', now())->delete();
    }
}
