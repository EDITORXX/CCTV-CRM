<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Install — {{ config('app.name', 'MMS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #1a1c2e 0%, #4e73df 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .install-card { max-width: 520px; width: 100%; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,.2); }
        .card-header { background: #1a1c2e; color: #fff; font-weight: 600; border-radius: 1rem 1rem 0 0; padding: 1rem 1.25rem; }
        .form-label { font-weight: 500; }
        .btn-install { background: #1a1c2e; color: #fff; font-weight: 600; padding: 0.65rem 1.5rem; }
        .btn-install:hover { background: #2d2f45; color: #fff; }
        .brand { text-align: center; color: #fff; margin-bottom: 1.5rem; }
        .brand h1 { font-size: 1.5rem; font-weight: 700; }
        .brand p { opacity: .9; font-size: .9rem; }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="brand">
            <h1><i class="bi bi-gear-fill me-2"></i>{{ config('app.name', 'MMS') }}</h1>
            <p>One-click server installation</p>
        </div>
        <div class="card">
            <div class="card-header">
                <i class="bi bi-database-gear me-2"></i> Database &amp; App Settings
            </div>
            <div class="card-body p-4">
                @if($errors->has('install'))
                    <div class="alert alert-danger">{{ $errors->first('install') }}</div>
                @endif
                <form action="{{ route('install.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="app_url" class="form-label">Application URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('app_url') is-invalid @enderror" id="app_url" name="app_url"
                                   value="{{ old('app_url', $appUrl) }}" placeholder="https://erp.yourdomain.com" required>
                            @error('app_url')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="db_host" class="form-label">Database Host <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('db_host') is-invalid @enderror" id="db_host" name="db_host"
                                   value="{{ old('db_host', $dbHost) }}" placeholder="127.0.0.1 or localhost" required>
                            @error('db_host')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="db_port" class="form-label">Database Port</label>
                            <input type="text" class="form-control @error('db_port') is-invalid @enderror" id="db_port" name="db_port"
                                   value="{{ old('db_port', $dbPort) }}" placeholder="3306">
                            @error('db_port')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12">
                            <label for="db_database" class="form-label">Database Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('db_database') is-invalid @enderror" id="db_database" name="db_database"
                                   value="{{ old('db_database', $dbName) }}" placeholder="e.g. u123_mms" required>
                            @error('db_database')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="db_username" class="form-label">Database Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('db_username') is-invalid @enderror" id="db_username" name="db_username"
                                   value="{{ old('db_username', $dbUser) }}" required>
                            @error('db_username')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="db_password" class="form-label">Database Password</label>
                            <input type="password" class="form-control @error('db_password') is-invalid @enderror" id="db_password" name="db_password"
                                   value="{{ old('db_password', $dbPass) }}" placeholder="Leave blank if none">
                            @error('db_password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-install w-100" id="btnInstall">
                                <i class="bi bi-lightning-charge-fill me-2"></i> Install Now
                            </button>
                        </div>
                    </div>
                </form>
                <p class="text-muted small mt-3 mb-0">
                    This will create/update .env, run migrations, and create the storage link. Make sure the database exists on your server.
                </p>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function() {
            var btn = document.getElementById('btnInstall');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Installing...';
        });
    </script>
</body>
</html>
