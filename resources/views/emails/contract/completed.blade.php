<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Contract Completed</title>
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
            background-color: #d1ecf1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #bee5eb;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .success-icon {
            font-size: 48px;
            color: #17a2b8;
            margin-bottom: 10px;
        }
        .attachment-notice {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">🎉</div>
        <h1>Employment Contract Completed!</h1>
        <p>Contract Number: <strong>{{ $contract->contract_number }}</strong></p>
    </div>

    <div class="content">
        <h2>Congratulations {{ $employeeName }}!</h2>
        
        <p>Your employment contract has been fully processed and completed. You are now officially part of the Pizzaburg team!</p>
        
        <h3>Contract Summary:</h3>
        <ul>
            <li><strong>Position:</strong> {{ $invite->position->name }}</li>
            <li><strong>Branch:</strong> {{ $invite->branch->name }}</li>
            <li><strong>Start Date:</strong> {{ $invite->joining_date->format('M d, Y') }}</li>
            <li><strong>Completed Date:</strong> {{ $contract->completed_at->format('M d, Y \a\t g:i A') }}</li>
            <li><strong>Contract Number:</strong> {{ $contract->contract_number }}</li>
        </ul>
        
        <div class="attachment-notice">
            <h4>📎 Contract Copy Attached</h4>
            <p>Please find your completed and signed employment contract attached to this email. Please keep this document safe for your records.</p>
        </div>
        
        <h3>Next Steps:</h3>
        <p>As your start date approaches, you will receive:</p>
        <ul>
            <li>Welcome package with company information</li>
            <li>First-day instructions and schedule</li>
            <li>Access credentials for company systems</li>
            <li>Contact information for your manager</li>
        </ul>
        
        <h3>Important Information:</h3>
        <ul>
            <li>Please arrive 15 minutes early on your first day</li>
            <li>Bring a valid ID and any required documents</li>
            <li>Dress code: Business casual</li>
            <li>Parking information will be provided separately</li>
        </ul>
        
        <p>If you have any questions before your start date, please contact our HR team.</p>
        
        <p>We're excited to have you join our team!</p>
    </div>

    <div class="footer">
        <p>Welcome to Pizzaburg!<br>
        <strong>Pizzaburg HR Team</strong></p>
        
        <p><em>This is an automated message. Please do not reply to this email.</em></p>
    </div>
</body>
</html>
