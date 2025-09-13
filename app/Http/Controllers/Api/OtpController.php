<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {}

    /**
     * Send OTP (FR-010)
     */
    public function send(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required_without:email|string|max:20',
            'email' => 'required_without:phone|email|max:255',
            'type' => 'string|in:login,verification,recovery',
            'purpose' => 'string|in:onboarding,login,password_reset',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->otpService->sendOtp(
            phone: $request->input('phone'),
            email: $request->input('email'),
            type: $request->input('type', 'login'),
            purpose: $request->input('purpose', 'onboarding'),
            request: $request
        );

        $statusCode = $result['success'] ? 200 : 429;
        
        return response()->json($result, $statusCode);
    }

    /**
     * Verify OTP (FR-011)
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6',
            'phone' => 'required_without:email|string|max:20',
            'email' => 'required_without:phone|email|max:255',
            'type' => 'string|in:login,verification,recovery',
            'purpose' => 'string|in:onboarding,login,password_reset',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->otpService->verifyOtp(
            code: $request->input('code'),
            phone: $request->input('phone'),
            email: $request->input('email'),
            type: $request->input('type', 'login'),
            purpose: $request->input('purpose', 'onboarding'),
            request: $request
        );

        $statusCode = $result['success'] ? 200 : 400;
        
        return response()->json($result, $statusCode);
    }

    /**
     * Resend OTP
     */
    public function resend(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required_without:email|string|max:20',
            'email' => 'required_without:phone|email|max:255',
            'type' => 'string|in:login,verification,recovery',
            'purpose' => 'string|in:onboarding,login,password_reset',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->otpService->sendOtp(
            phone: $request->input('phone'),
            email: $request->input('email'),
            type: $request->input('type', 'login'),
            purpose: $request->input('purpose', 'onboarding'),
            request: $request
        );

        $statusCode = $result['success'] ? 200 : 429;
        
        return response()->json($result, $statusCode);
    }

    /**
     * Check OTP status
     */
    public function status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required_without:email|string|max:20',
            'email' => 'required_without:phone|email|max:255',
            'type' => 'string|in:login,verification,recovery',
            'purpose' => 'string|in:onboarding,login,password_reset',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // TODO: Implement OTP status check
        return response()->json([
            'success' => true,
            'message' => 'OTP status check not implemented yet',
        ]);
    }
}
