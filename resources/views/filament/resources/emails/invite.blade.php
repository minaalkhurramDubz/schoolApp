<!DOCTYPE html>
<html>
<head>
    <title>Welcome!</title>
</head>
<body>
    <p>Hello {{ $user->name }},</p>

    <p>You’ve been invited to the system as a {{ $user->role }}.</p>

    <p>Please login using your email address:</p>
    <p>{{ $user->email }}</p>

    <p>Thanks!</p>
</body>
</html>
