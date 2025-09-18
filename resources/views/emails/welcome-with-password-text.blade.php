Welcome to Pizzaburg!

Hello {{ $user->name }},

Welcome to the Pizzaburg team! We're excited to have you join us.

Your employee portal account has been created. Here are your login credentials:

Email: {{ $user->email }}
Temporary Password: {{ $temporaryPassword }}

IMPORTANT SECURITY NOTICE:
For security reasons, you MUST change your password on your first login. 
This temporary password will not work after your first login.

To access your employee portal:
1. Visit: {{ $loginUrl }}
2. Enter your email and temporary password
3. You will be prompted to create a new password
4. Complete your profile setup

If you have any questions or need assistance, please contact the HR department.

Best regards,
The Pizzaburg HR Team

---
This is an automated message. Please do not reply to this email.
© {{ date('Y') }} Pizzaburg. All rights reserved.