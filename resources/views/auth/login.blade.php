<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — {{ config('app.name', 'CCTV Management') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a1c2e 0%, #4e73df 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .brand-area {
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
        }

        .brand-area .icon-circle {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,.15);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: .75rem;
            backdrop-filter: blur(4px);
        }

        .brand-area h3 {
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .brand-area p {
            opacity: .65;
            font-size: .875rem;
            margin: 0;
        }

        .login-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            padding: 2rem;
        }

        .login-card .form-label {
            font-weight: 500;
            font-size: .875rem;
            color: #555;
        }

        .login-card .form-control {
            padding: .7rem 1rem;
            border-radius: .5rem;
            border: 1.5px solid #dee2e6;
            font-size: .9rem;
        }

        .login-card .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 .2rem rgba(78,115,223,.15);
        }

        .btn-login {
            background: linear-gradient(135deg, #4e73df, #224abe);
            border: none;
            padding: .7rem;
            font-weight: 600;
            font-size: .95rem;
            border-radius: .5rem;
            width: 100%;
            color: #fff;
            transition: opacity .2s;
        }

        .btn-login:hover {
            opacity: .9;
            color: #fff;
        }

        .input-icon-group {
            position: relative;
        }

        .input-icon-group .form-control {
            padding-left: 2.75rem;
        }

        .input-icon-group .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 1rem;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="brand-area">
            <div class="icon-circle">
                <i class="bi bi-camera-video-fill"></i>
            </div>
            <h3>CCTV Management</h3>
            <p>Sign in to your account</p>
        </div>

        <div class="login-card">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-icon-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="you@example.com"
                               required
                               autocomplete="email"
                               autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-icon-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               required
                               autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="remember">Remember Me</label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small text-decoration-none">
                            Forgot Password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </button>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="{{ route('quick-login') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-lightning-charge-fill me-1"></i> Quick Demo Login
            </a>
        </div>

        <p class="text-center mt-3 small" style="color: rgba(255,255,255,.4);">
            &copy; {{ date('Y') }} CCTV Management System
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
