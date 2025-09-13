<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\KycController;

Route::get('/', function () {
    return view('welcome');
});

// Contract signing routes
Route::prefix('contract')->group(function () {
    Route::get('/{token}', [ContractController::class, 'show'])->name('contract.show');
    Route::post('/{token}/accept', [ContractController::class, 'accept'])->name('contract.accept');
    Route::get('/{token}/download', [ContractController::class, 'download'])->name('contract.download');
});

// KYC verification routes
Route::prefix('kyc')->group(function () {
    Route::get('/{token}', [KycController::class, 'show'])->name('kyc.show');
    Route::post('/{token}/verify', [KycController::class, 'verifySelfie'])->name('kyc.verify');
    Route::get('/status/{verification_id}', [KycController::class, 'status'])->name('kyc.status');
    Route::post('/{token}/retry', [KycController::class, 'retry'])->name('kyc.retry');
});

// Private file serving route (for admin only)
Route::middleware(['auth'])->group(function () {
    Route::get('/private/{path}', function ($path) {
        // Only allow access to KYC images
        if (!str_starts_with($path, 'kyc/')) {
            abort(403, 'Access denied');
        }
        
        if (!Storage::disk('private')->exists($path)) {
            abort(404, 'File not found');
        }
        
        $file = Storage::disk('private')->get($path);
        $mimeType = mime_content_type(Storage::disk('private')->path($path));
        
        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    })->where('path', '.*')->name('private.file');
});
