<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SmsController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send SMS using default provider
     */
    public function sendSms(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'message' => 'required|string|max:160',
        ]);

        $result = $this->smsService->sendSms(
            $request->input('mobile'),
            $request->input('message')
        );

        return response()->json($result);
    }

    /**
     * Send SMS using specific provider
     */
    public function sendSmsWithProvider(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string',
            'mobile' => 'required|string',
            'message' => 'required|string|max:160',
        ]);

        $result = $this->smsService->sendSmsWithProvider(
            $request->input('provider'),
            $request->input('mobile'),
            $request->input('message')
        );

        return response()->json($result);
    }

    /**
     * Send SMS with queue
     */
    public function sendSmsWithQueue(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'message' => 'required|string|max:160',
            'provider' => 'nullable|string',
        ]);

        $result = $this->smsService->sendSmsWithQueue(
            $request->input('mobile'),
            $request->input('message'),
            $request->input('provider')
        );

        return response()->json($result);
    }

    /**
     * Test SMS provider
     */
    public function testProvider(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string',
            'mobile' => 'nullable|string',
        ]);

        $result = $this->smsService->testProvider(
            $request->input('provider'),
            $request->input('mobile', '01700000000')
        );

        return response()->json($result);
    }

    /**
     * Get all active providers
     */
    public function getActiveProviders(): JsonResponse
    {
        $providers = $this->smsService->getActiveProviders();

        return response()->json([
            'success' => true,
            'providers' => $providers,
        ]);
    }
}
