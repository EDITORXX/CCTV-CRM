<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CCTV View — Enter Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .pass-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            padding: 2.5rem 2rem;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .stream-icon {
            width: 70px;
            height: 70px;
            background: rgba(59,130,246,.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.75rem;
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="pass-card">
        <div class="stream-icon">
            <i class="bi bi-camera-video"></i>
        </div>
        <h4 class="fw-bold mb-1">CCTV View</h4>
        <p class="text-muted small mb-4">Enter the password to watch this stream</p>

        @if($errors->any())
            <div class="alert alert-danger small py-2 mb-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('livestream.verify', $stream->token) }}">
            @csrf
            <div class="mb-3">
                <input type="password" class="form-control form-control-lg text-center" name="password"
                       placeholder="Enter password" required autofocus autocomplete="off"
                       style="letter-spacing:.15em; font-size:1.1rem;">
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-play-circle me-1"></i> Watch Stream
            </button>
        </form>

        <p class="text-muted small mt-4 mb-0">
            <i class="bi bi-shield-lock me-1"></i> This stream is password protected
        </p>
    </div>
</body>
</html>
