<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Pizzaburg</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .password-box {
            background-color: #fff;
            border: 2px solid #dc2626;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
        .password {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            color: #dc2626;
        }
        .button {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🍕 Welcome to Pizzaburg!</h1>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Welcome to the Pizzaburg team! We're excited to have you join us.</p>
        
        <p>Your employee portal account has been created. Here are your login credentials:</p>
        
        <div class="password-box">
            <strong>Email:</strong> {{ $user->email }}<br>
            <strong>Temporary Password:</strong><br>
            <span class="password">{{ $temporaryPassword }}</span>
        </div>
        
        <div class="warning">
            <strong>⚠️ Important Security Notice:</strong><br>
            For security reasons, you <strong>must change your password</strong> on your first login. 
            This temporary password will not work after your first login.
        </div>
        
        <p>To access your employee portal:</p>
        <ol>
            <li>Click the login button below</li>
            <li>Enter your email and temporary password</li>
            <li>You will be prompted to create a new password</li>
            <li>Complete your profile setup</li>
        </ol>
        
        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="button">Access Employee Portal</a>
        </div>
        
        <p>If you have any questions or need assistance, please contact the HR department.</p>
        
        <p>Best regards,<br>
        The Pizzaburg HR Team</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>© {{ date('Y') }} Pizzaburg. All rights reserved.</p>
    </div>
</body>
</html>
