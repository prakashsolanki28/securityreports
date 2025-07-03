<!DOCTYPE html>
<html>
<head>
    <title>Your OTP Code</title>
</head>
<body>
    <h2>Hello!</h2>
    <p>Your one-time password (OTP) is:</p>

    <h1 style="font-size: 2rem; color: teal;">{{ $otp }}</h1>

    <p>This OTP is valid for 10 minutes. Do not share it with anyone.</p>

    <br>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>