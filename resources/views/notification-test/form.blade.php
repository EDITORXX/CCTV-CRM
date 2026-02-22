<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Notification — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 2rem; max-width: 480px; margin: 0 auto; font-family: system-ui, sans-serif; }
        .card { border-radius: 0.5rem; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
    </style>
</head>
<body>
    <div class="card p-4">
        <h5 class="mb-3">Test Notification</h5>
        <p class="text-muted small mb-3">Send a test email to verify mail/notification setup.</p>
        <form action="{{ url()->current() }}" method="GET">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control mb-3" placeholder="your@email.com" required>
            <button type="submit" class="btn btn-primary">Send test email</button>
        </form>
        <p class="text-muted small mt-3 mb-0">Or open: <code>{{ url()->current() }}?email=your@email.com</code></p>
    </div>
</body>
</html>
