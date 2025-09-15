<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Ready for Signature</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
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
        <h1>📄 Employment Contract Ready</h1>
        <p>Contract Number: <strong>{{ $contract->contract_number }}</strong></p>
    </div>

    <div class="content">
        <h2>Hello {{ $employeeName }},</h2>
        
        <p>Your employment contract is now ready for your review and signature. Please take a moment to review the terms and conditions carefully.</p>
        
        <h3>Contract Details:</h3>
        <ul>
            <li><strong>Position:</strong> {{ $invite->position?->name ?? 'N/A' }}</li>
            <li><strong>Branch:</strong> {{ $invite->branch?->name ?? 'N/A' }}</li>
            <li><strong>Contract Number:</strong> {{ $contract->contract_number }}</li>
        </ul>
        
        <p>To proceed with signing your contract, please click the button below:</p>
        
        <div style="text-align: center;">
            <a href="{{ $contractUrl }}" class="button">Review & Sign Contract</a>
        </div>
        
        <p><strong>Important:</strong> Please review all terms and conditions before signing. If you have any questions, please contact HR.</p>
        
        <p>This link will remain valid for 7 days from the date of this email.</p>
    </div>

    <div class="footer">
        <p>Best regards,<br>
        <strong>Pizzaburg HR Team</strong></p>
        
        <p><em>This is an automated message. Please do not reply to this email.</em></p>
    </div>
</body>
</html>
