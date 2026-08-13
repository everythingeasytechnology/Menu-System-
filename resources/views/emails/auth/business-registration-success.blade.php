<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h1 style="font-size: 20px;">Registration Successful</h1>
    <p>Hello {{ $user->name }},</p>
    <p>Your business <strong>{{ $business->name }}</strong> has been registered successfully.</p>
    <p>You can now login and start setting up your menu, service points, staff, and orders.</p>
    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
