<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Log Configuration
    |--------------------------------------------------------------------------
    |
    | SMS Log will save sms request, provider name, and response in database table called `lbs_log`
    | You can change sms log to true/false according to your need. Default is set to true
    |
    */
    'sms_log' => env('SMS_LOG', true),

    /*
    |--------------------------------------------------------------------------
    | SMS Log Driver
    |--------------------------------------------------------------------------
    |
    | Sms log will be saved in database(lbs_log table) or file(storage/logs/laravel.log).
    | You can choose one according to need
    |
    */
    'log_driver' => env('SMS_LOG_DRIVER', 'database'), //database, file

    /*
    |--------------------------------------------------------------------------
    | Default SMS Provider
    |--------------------------------------------------------------------------
    |
    | Default provider will be used during usage of facade( Xenon\LaravelBDSms\Facades\SMS )
    |
    */
    'default_provider' => env('SMS_DEFAULT_PROVIDER', 'Xenon\LaravelBDSms\Provider\Ssl'),

    /*
    |--------------------------------------------------------------------------
    | SMS Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Providers are companies or gateways those provide sms credentials as well as sell sms to customers.
    | This providers key store all the necessary credentials needed for using inside .env file; Be sure to use this
    | credentials in your .env file before sending sms. This will be used while you are sending sms using
    | facade(Xenon\LaravelBDSms\Facades\SMS)
    |
    */
    'providers' => [
        'Xenon\LaravelBDSms\Provider\Ssl' => [
            'api_token' => env('SMS_SSL_API_TOKEN', ''),
            'sid' => env('SMS_SSL_SID', ''),
            'csms_id' => env('SMS_SSL_CSMS_ID', ''),
        ],
        'Xenon\LaravelBDSms\Provider\MimSms' => [
            'ApiKey' => env('SMS_MIM_SMS_API_KEY', ''),
            'SenderName' => env('SMS_MIM_SMS_SENDER_NAME', ''),
            'UserName' => env('SMS_MIM_SMS_API_USERNAME', ''),
        ],
        'Xenon\LaravelBDSms\Provider\Alpha' => [
            'api_key' => env('SMS_ALPHA_SMS_API_KEY', ''),
        ],
        'Xenon\LaravelBDSms\Provider\Banglalink' => [
            'userID' => env('SMS_BANGLALINK_USERID', ''),
            'passwd' => env('SMS_BANGLALINK_PASSWD', ''),
            'sender' => env('SMS_BANGLALINK_SENDER', ''),
        ],
        'Xenon\LaravelBDSms\Provider\BoomCast' => [
            'username' => env('SMS_BOOM_CAST_USERNAME', ''),
            'password' => env('SMS_BOOM_CAST_PASSWORD', ''),
            'masking' => env('SMS_BOOM_CAST_MASKING', ''),
        ],
        // Add more providers as needed
    ],
];
