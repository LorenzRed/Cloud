<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #333;">
    <p>Hello,</p>
    <p>Use this code to reset your password for <strong>{{ $email }}</strong>:</p>
    <p style="font-size: 1.75rem; font-weight: 700; letter-spacing: 0.25em; color: #4A90E2;">{{ $otp }}</p>
    <p style="font-size: 0.875rem; color: #666;">This code expires in 15 minutes. If you did not request a reset, you can ignore this email.</p>
    <p style="font-size: 0.875rem; color: #666;">— {{ config('app.name') }}</p>
</body>
</html>
