<!DOCTYPE html>
<html>

<head>
    <title>Login OTP</title>
</head>

<body>
    <p>Hello {{ $user->name }},</p>
    <p>Your OTP code for login is: <strong>{{ $code }}</strong></p>
    <p>This code will expire in 5 minutes.</p>
    <p>Thank you.</p>
</body>

</html>