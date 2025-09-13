<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Onboarding Routes (Simplified - No OTP)
Route::prefix('onboarding')->group(function () {
    Route::get('/invite/{token}', function ($token) {
        // TODO: Implement onboarding invite validation and form display
        return response()->json([
            'message' => 'Onboarding invite endpoint - to be implemented',
            'token' => $token,
        ]);
    });
});

// SMS Routes
Route::prefix('sms')->group(function () {
    Route::post('/send', [SmsController::class, 'sendSms']);
    Route::post('/send-with-provider', [SmsController::class, 'sendSmsWithProvider']);
    Route::post('/send-with-queue', [SmsController::class, 'sendSmsWithQueue']);
    Route::post('/test-provider', [SmsController::class, 'testProvider']);
    Route::get('/providers', [SmsController::class, 'getActiveProviders']);
});

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
    ]);
});
