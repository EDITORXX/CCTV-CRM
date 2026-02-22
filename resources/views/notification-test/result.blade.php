<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Notification — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; max-width: 520px; margin: 0 auto; font-family: system-ui, sans-serif; }
        .card { border-radius: 0.5rem; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
    </style>
</head>
<body>
    <div class="card p-4">
        <h5 class="mb-3">Test Notification</h5>
        <p class="mb-2">{{ $message }}</p>
        @if(isset($email))<p class="text-muted small mb-0">Check inbox (and spam) for: {{ $email }}</p>@endif
        <a href="{{ url('/notification-test') }}" class="btn btn-outline-primary btn-sm mt-3">Send another test</a>
    </div>
</body>
</html>
