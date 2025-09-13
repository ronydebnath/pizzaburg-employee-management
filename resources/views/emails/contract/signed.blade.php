<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Signed Successfully</title>
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
            background-color: #d4edda;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 10px;
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
        <div class="success-icon">✅</div>
        <h1>Contract Signed Successfully!</h1>
        <p>Contract Number: <strong>{{ $contract->contract_number }}</strong></p>
    </div>

    <div class="content">
        <h2>Congratulations {{ $employeeName }}!</h2>
        
        <p>Your employment contract has been successfully signed and is now being processed by our HR team.</p>
        
        <h3>Contract Details:</h3>
        <ul>
            <li><strong>Position:</strong> {{ $invite->position->name }}</li>
            <li><strong>Branch:</strong> {{ $invite->branch->name }}</li>
            <li><strong>Start Date:</strong> {{ $invite->joining_date->format('M d, Y') }}</li>
            <li><strong>Signed Date:</strong> {{ $contract->signed_at->format('M d, Y \a\t g:i A') }}</li>
            <li><strong>Contract Number:</strong> {{ $contract->contract_number }}</li>
        </ul>
        
        <h3>What's Next?</h3>
        <p>Our HR team will now:</p>
        <ul>
            <li>Process your signed contract</li>
            <li>Generate the final employment documents</li>
            <li>Send you a copy of the completed contract</li>
            <li>Prepare for your first day</li>
        </ul>
        
        <p>You will receive a final confirmation email with your completed contract attached once processing is complete.</p>
        
        <p>If you have any questions, please don't hesitate to contact our HR team.</p>
    </div>

    <div class="footer">
        <p>Welcome to the Pizzaburg team!<br>
        <strong>Pizzaburg HR Team</strong></p>
        
        <p><em>This is an automated message. Please do not reply to this email.</em></p>
    </div>
</body>
</html>
