<?php

namespace App\Services;

use App\Models\SmsSetting;
use Illuminate\Support\Facades\Log;
use Xenon\LaravelBDSms\Facades\SMS;
use Xenon\LaravelBDSms\Sender;

class SmsService
{
    /**
     * Send SMS using the default provider
     */
    public function sendSms(string|array $mobile, string $message): array
    {
        try {
            $defaultProvider = SmsSetting::getDefaultProvider();
            
            if (!$defaultProvider) {
                throw new \Exception('No default SMS provider configured');
            }

            if (!$defaultProvider->is_active) {
                throw new \Exception('Default SMS provider is not active');
            }

            // Update the config with our database credentials
            $this->updateConfigForProvider($defaultProvider);

            // Use the facade with specific provider
            $response = SMS::via($defaultProvider->provider_class)
                ->shoot($mobile, $message);

            $this->logSms($mobile, $message, $defaultProvider->provider_name, $response);

            return [
                'success' => true,
                'provider' => $defaultProvider->provider_name,
                'response' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage(), [
                'mobile' => $mobile,
                'message' => $message,
                'provider' => $defaultProvider->provider_name ?? 'Unknown',
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $defaultProvider->provider_name ?? 'Unknown',
            ];
        }
    }

    /**
     * Send SMS using a specific provider
     */
    public function sendSmsWithProvider(string $providerName, string|array $mobile, string $message): array
    {
        try {
            $provider = SmsSetting::where('provider_name', $providerName)
                ->where('is_active', true)
                ->first();

            if (!$provider) {
                throw new \Exception("Provider '{$providerName}' not found or not active");
            }

            // Update the config with our database credentials
            $this->updateConfigForProvider($provider);

            $response = SMS::via($provider->provider_class)
                ->shoot($mobile, $message);

            $this->logSms($mobile, $message, $provider->provider_name, $response);

            return [
                'success' => true,
                'provider' => $provider->provider_name,
                'response' => $response,
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage(), [
                'mobile' => $mobile,
                'message' => $message,
                'provider' => $providerName,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $providerName,
            ];
        }
    }

    /**
     * Send SMS with queue
     */
    public function sendSmsWithQueue(string|array $mobile, string $message, string $providerName = null): array
    {
        try {
            $provider = $providerName 
                ? SmsSetting::where('provider_name', $providerName)->where('is_active', true)->first()
                : SmsSetting::getDefaultProvider();

            if (!$provider) {
                throw new \Exception('No active SMS provider found');
            }

            // Update the config with our database credentials
            $this->updateConfigForProvider($provider);

            SMS::via($provider->provider_class)
                ->shootWithQueue($mobile, $message);

            return [
                'success' => true,
                'provider' => $provider->provider_name,
                'queued' => true,
            ];

        } catch (\Exception $e) {
            Log::error('SMS queue failed: ' . $e->getMessage(), [
                'mobile' => $mobile,
                'message' => $message,
                'provider' => $providerName ?? 'Default',
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $providerName ?? 'Default',
            ];
        }
    }

    /**
     * Test SMS provider configuration
     */
    public function testProvider(string $providerName, string $testMobile = '01700000000'): array
    {
        try {
            $provider = SmsSetting::where('provider_name', $providerName)->first();

            if (!$provider) {
                throw new \Exception("Provider '{$providerName}' not found");
            }

            // Update the config with our database credentials
            $this->updateConfigForProvider($provider);

            $response = SMS::via($provider->provider_class)
                ->shoot($testMobile, 'Test SMS from Pizza App - ' . now()->format('Y-m-d H:i:s'));

            return [
                'success' => true,
                'provider' => $provider->provider_name,
                'response' => $response,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $providerName,
            ];
        }
    }

    /**
     * Get all active providers
     */
    public function getActiveProviders(): array
    {
        return SmsSetting::getActiveProviders()->map(function ($provider) {
            return [
                'id' => $provider->id,
                'name' => $provider->provider_name,
                'class' => $provider->provider_class,
                'is_default' => $provider->is_default,
                'credentials_count' => count(is_array($provider->credentials) ? $provider->credentials : []),
            ];
        })->toArray();
    }

    /**
     * Update the LaravelBDSms config with database credentials
     */
    private function updateConfigForProvider(SmsSetting $provider): void
    {
        $formattedCredentials = $this->formatCredentials($provider->credentials);
        
        // Update the config for this provider
        config(['sms.providers.' . $provider->provider_class => $formattedCredentials]);
    }

    /**
     * Format credentials from database format to LaravelBDSms format
     */
    private function formatCredentials($credentials): array
    {
        // Handle string input (JSON)
        if (is_string($credentials)) {
            $credentials = json_decode($credentials, true) ?? [];
        }
        
        // Ensure we have an array
        if (!is_array($credentials)) {
            return [];
        }
        
        $formatted = [];
        
        foreach ($credentials as $credential) {
            if (isset($credential['key']) && isset($credential['value'])) {
                $formatted[$credential['key']] = $credential['value'];
            }
        }

        return $formatted;
    }

    /**
     * Log SMS activity
     */
    private function logSms(string|array $mobile, string $message, string $provider, mixed $response): void
    {
        Log::info('SMS sent successfully', [
            'mobile' => is_array($mobile) ? implode(',', $mobile) : $mobile,
            'message' => $message,
            'provider' => $provider,
            'response' => $response,
            'timestamp' => now(),
        ]);
    }
}
