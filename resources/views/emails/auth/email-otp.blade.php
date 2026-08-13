<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h1 style="font-size: 20px;">Email Verification OTP</h1>
    <p>Your OTP is:</p>
    <p style="font-size: 28px; font-weight: 700; letter-spacing: 4px;">{{ $otp }}</p>
    <p>This OTP will expire in {{ (int) ($expiresInSeconds / 60) }} minutes.</p>
    <p>If you did not request this OTP, you can ignore this email.</p>
</body>
</html>
