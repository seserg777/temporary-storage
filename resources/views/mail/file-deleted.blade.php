<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Deleted</title>
</head>
<body>
    <p>Hello,</p>

    <p>
        The file <strong>{{ $originalName }}</strong> was deleted from
        {{ config('app.name') }} on {{ $deletedAt }}.
    </p>

    <p>This is an automated notification. No action is required.</p>
</body>
</html>