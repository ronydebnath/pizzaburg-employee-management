<?php

namespace Database\Seeders;

use App\Models\SmsSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmsSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'provider_name' => 'SSL Wireless',
                'provider_class' => 'Xenon\LaravelBDSms\Provider\Ssl',
                'is_active' => false,
                'is_default' => false,
                'description' => 'SSL Wireless SMS Gateway - One of the most popular SMS providers in Bangladesh',
                'credentials' => [
                    ['key' => 'api_token', 'value' => ''],
                    ['key' => 'sid', 'value' => ''],
                    ['key' => 'csms_id', 'value' => ''],
                ],
            ],
            [
                'provider_name' => 'MimSMS',
                'provider_class' => 'Xenon\LaravelBDSms\Provider\MimSms',
                'is_active' => false,
                'is_default' => false,
                'description' => 'MimSMS - Reliable SMS service provider in Bangladesh',
                'credentials' => [
                    ['key' => 'ApiKey', 'value' => ''],
                    ['key' => 'SenderName', 'value' => ''],
                    ['key' => 'UserName', 'value' => ''],
                ],
            ],
            [
                'provider_name' => 'Alpha SMS',
                'provider_class' => 'Xenon\LaravelBDSms\Provider\Alpha',
                'is_active' => false,
                'is_default' => false,
                'description' => 'Alpha SMS - Advanced SMS service with multiple features',
                'credentials' => [
                    ['key' => 'api_key', 'value' => ''],
                ],
            ],
            [
                'provider_name' => 'Banglalink',
                'provider_class' => 'Xenon\LaravelBDSms\Provider\Banglalink',
                'is_active' => false,
                'is_default' => false,
                'description' => 'Banglalink SMS Gateway',
                'credentials' => [
                    ['key' => 'userID', 'value' => ''],
                    ['key' => 'passwd', 'value' => ''],
                    ['key' => 'sender', 'value' => ''],
                ],
            ],
            [
                'provider_name' => 'BoomCast',
                'provider_class' => 'Xenon\LaravelBDSms\Provider\BoomCast',
                'is_active' => false,
                'is_default' => false,
                'description' => 'BoomCast SMS Gateway',
                'credentials' => [
                    ['key' => 'username', 'value' => ''],
                    ['key' => 'password', 'value' => ''],
                    ['key' => 'masking', 'value' => ''],
                ],
            ],
        ];

        foreach ($providers as $provider) {
            SmsSetting::create($provider);
        }
    }
}
