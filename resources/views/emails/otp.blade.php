<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: #fff; margin: 0;">Crutox</h1>
        <p style="color: #fff; margin: 10px 0 0 0;">Password Reset Request</p>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #ddd; border-top: none;">
        <h2 style="color: #333; margin-top: 0;">Hello!</h2>
        
        <p>You have requested to reset your password. Please use the following OTP code to verify your identity:</p>
        
        <div style="background: #fff; border: 2px dashed #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0;">
            <p style="font-size: 14px; color: #666; margin: 0 0 10px 0;">Your OTP Code:</p>
            <h1 style="font-size: 36px; color: #667eea; margin: 0; letter-spacing: 5px; font-weight: bold;">{{ $otp }}</h1>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            <strong>Important:</strong>
            <ul style="color: #666; font-size: 14px;">
                <li>This OTP is valid for a limited time</li>
                <li>Do not share this code with anyone</li>
                <li>If you didn't request this, please ignore this email</li>
            </ul>
        </p>
        
        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            Best regards,<br>
            <strong>Crutox Team</strong>
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #999; font-size: 12px;">
        <p>This is an automated email. Please do not reply.</p>
    </div>
</body>
</html>
