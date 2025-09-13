# SMS Gateway Integration

This document describes the SMS gateway integration system built for the Pizza application, inspired by the LaravelBDSms package.

## Overview

The SMS integration provides a comprehensive solution for managing and using multiple Bangladeshi SMS providers through a Filament admin interface. It supports over 30+ SMS providers and allows dynamic configuration without code changes.

## Features

- **Multiple Provider Support**: Support for 30+ Bangladeshi SMS providers
- **Dynamic Configuration**: Configure providers through Filament admin interface
- **Default Provider Management**: Set and manage default SMS providers
- **Provider Testing**: Test SMS providers directly from the admin interface
- **Queue Support**: Send SMS messages using Laravel queues
- **Comprehensive Logging**: Log all SMS activities for monitoring
- **API Endpoints**: RESTful API for SMS operations

## Supported SMS Providers

The system supports the following SMS providers:

- SSL Wireless
- MimSMS
- Alpha SMS
- Banglalink
- BoomCast
- BulkSMS BD
- Diana Host
- Diana SMS
- ESMS
- Grameenphone
- Infobip
- Lpeek
- MDL
- Metronet
- Mobireach
- Muthofun
- Novocom BD
- Onnorokom SMS
- Quick SMS
- SendMySMS
- SmartLab SMS
- SMS4BD
- SMS Bangladesh
- SMSinBD
- SMS Net24
- SMS Net BD
- SMS Q
- SongBird
- Tense
- TruboSMS
- 24 Bulk SMS
- 24 Bulk SMS BD
- Viatech
- WinText
- Zaman IT
- Custom Gateway

## Installation & Setup

### 1. Database Migration

Run the migration to create the SMS settings table:

```bash
php artisan migrate
```

### 2. Seed Default Providers

Run the seeder to populate default SMS providers:

```bash
php artisan db:seed --class=SmsSettingSeeder
```

### 3. Install LaravelBDSms Package

Add the LaravelBDSms package to your composer.json:

```bash
composer require xenon/laravelbdsms
```

Publish the package configuration:

```bash
php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider --tag="config"
```

## Usage

### Filament Admin Interface

1. **Access SMS Settings**: Navigate to Settings > SMS Gateways in your Filament admin panel
2. **Configure Providers**: Add or edit SMS providers with their credentials
3. **Set Default Provider**: Choose which provider to use as default
4. **Test Providers**: Use the test button to verify provider configuration

### Programmatic Usage

#### Using the SmsService

```php
use App\Services\SmsService;

$smsService = app(SmsService::class);

// Send SMS using default provider
$result = $smsService->sendSms('01700000000', 'Hello World!');

// Send SMS using specific provider
$result = $smsService->sendSmsWithProvider('SSL Wireless', '01700000000', 'Hello World!');

// Send SMS with queue
$result = $smsService->sendSmsWithQueue('01700000000', 'Hello World!');

// Test provider
$result = $smsService->testProvider('SSL Wireless', '01700000000');
```

#### Using API Endpoints

**Send SMS (Default Provider)**
```bash
POST /api/sms/send
Content-Type: application/json

{
    "mobile": "01700000000",
    "message": "Hello World!"
}
```

**Send SMS (Specific Provider)**
```bash
POST /api/sms/send-with-provider
Content-Type: application/json

{
    "provider": "SSL Wireless",
    "mobile": "01700000000",
    "message": "Hello World!"
}
```

**Send SMS with Queue**
```bash
POST /api/sms/send-with-queue
Content-Type: application/json

{
    "mobile": "01700000000",
    "message": "Hello World!",
    "provider": "SSL Wireless"
}
```

**Test Provider**
```bash
POST /api/sms/test-provider
Content-Type: application/json

{
    "provider": "SSL Wireless",
    "mobile": "01700000000"
}
```

**Get Active Providers**
```bash
GET /api/sms/providers
```

### Integration with Existing Code

#### Onboarding Invitation Integration

The SMS service can be easily integrated with the onboarding invitation system:

```php
// In your onboarding controller
use App\Services\SmsService;

public function sendInvitation(Request $request)
{
    $invite = $this->createInvitation($request->all());
    $message = "Welcome to Pizzaburg! Complete your onboarding: {$invite->kyc_url}";
    
    $smsService = app(SmsService::class);
    $result = $smsService->sendSms($request->phone, $message);
    
    if ($result['success']) {
        return response()->json(['message' => 'Invitation sent successfully']);
    }
    
    return response()->json(['error' => 'Failed to send invitation'], 500);
}
```

## Configuration

### Provider Credentials

Each SMS provider requires specific credentials. Here are examples for popular providers:

#### SSL Wireless
```json
{
    "api_token": "your_api_token",
    "sid": "your_sid",
    "csms_id": "your_csms_id"
}
```

#### MimSMS
```json
{
    "ApiKey": "your_api_key",
    "SenderName": "your_sender_name",
    "UserName": "your_username"
}
```

#### Alpha SMS
```json
{
    "api_key": "your_api_key"
}
```

### Environment Variables

You can also configure providers using environment variables by adding them to your `.env` file:

```env
# SSL Wireless
SMS_SSL_API_TOKEN=your_api_token
SMS_SSL_SID=your_sid
SMS_SSL_CSMS_ID=your_csms_id

# MimSMS
SMS_MIM_SMS_API_KEY=your_api_key
SMS_MIM_SMS_SENDER_NAME=your_sender_name
SMS_MIM_SMS_API_USERNAME=your_username
```

## Database Schema

### sms_settings Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| provider_name | varchar(255) | Human-readable provider name |
| provider_class | varchar(255) | Full class name of the provider |
| is_active | boolean | Whether the provider is active |
| is_default | boolean | Whether this is the default provider |
| credentials | json | Provider credentials (key-value pairs) |
| description | text | Provider description |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

## Error Handling

The system provides comprehensive error handling:

- **Provider Not Found**: When a specified provider doesn't exist
- **Inactive Provider**: When trying to use an inactive provider
- **Missing Credentials**: When required credentials are not provided
- **API Errors**: When the SMS provider API returns an error
- **Network Errors**: When there are connectivity issues

## Logging

All SMS activities are logged with the following information:

- Mobile number(s)
- Message content
- Provider used
- Response from provider
- Timestamp
- Success/failure status

Logs are stored in Laravel's default log files and can be configured in `config/logging.php`.

## Security Considerations

1. **Credential Storage**: Credentials are stored in the database as JSON. Consider encrypting sensitive credentials.
2. **API Rate Limiting**: Implement rate limiting for SMS API endpoints.
3. **Input Validation**: All inputs are validated before processing.
4. **Access Control**: SMS settings should only be accessible to authorized admin users.

## Troubleshooting

### Common Issues

1. **Database Connection Error**: Ensure your database is properly configured and running.
2. **Provider Not Working**: Check credentials and provider status in the admin interface.
3. **Queue Not Processing**: Ensure Laravel queues are running (`php artisan queue:work`).

### Testing

Use the test functionality in the Filament admin interface to verify provider configuration before using in production.

## Future Enhancements

- SMS delivery reports
- Bulk SMS capabilities
- SMS templates
- Provider performance analytics
- Webhook support for delivery status
- SMS scheduling
- Multi-language support

## Support

For issues related to specific SMS providers, refer to their respective documentation or contact their support teams.

For system-related issues, check the Laravel logs and ensure all dependencies are properly installed and configured.
