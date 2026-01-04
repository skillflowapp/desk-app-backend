<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKILLFLOW OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            color: #333;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .message {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .otp-box {
            background: #f5f5f5;
            border: 2px dashed #667eea;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin: 25px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 5px;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            color: #f5576c;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            padding: 20px 30px;
            background: #f9f9f9;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SKILLFLOW</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Hello {{ $user->name }},
            </div>
            
            <div class="message">
                @if($type === 'email_verification')
                    Please verify your email address using this code:
                @else
                    Use this code to reset your password:
                @endif
            </div>
            
            <div class="otp-box">
                <div class="otp-code">{{ $code }}</div>
                <div class="expiry">Valid for {{ $type === 'email_verification' ? '15 minutes' : '10 minutes' }}</div>
            </div>
            
            <div class="warning">
                <strong>Security Notice:</strong> Never share this code with anyone. SKILLFLOW staff will never ask for your OTP.
            </div>
            
            <div style="color: #666; font-size: 14px; line-height: 1.6; margin-top: 20px;">
                <p>If you did not request this code, please ignore this email.</p>
                <p>Need help? Contact our support team at <a href="mailto:support@skillflow.com" style="color: #667eea; text-decoration: none;">support@skillflow.com</a></p>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} SKILLFLOW. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
