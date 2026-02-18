<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select Company — {{ config('app.name', 'CCTV Management') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a1c2e 0%, #2d2f45 50%, #4e73df 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .select-container {
            width: 100%;
            max-width: 540px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
            color: #fff;
        }

        .brand-header h2 {
            font-weight: 700;
            letter-spacing: .5px;
        }

        .brand-header p {
            opacity: .7;
            margin: 0;
        }

        .select-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            overflow: hidden;
        }

        .select-card .card-header-custom {
            background: #f8f9fc;
            padding: 1.5rem 2rem 1rem;
            border-bottom: 1px solid #e3e6ec;
        }

        .select-card .card-body-custom {
            padding: 1.5rem 2rem 2rem;
        }

        .company-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border: 2px solid #e3e6ec;
            border-radius: .75rem;
            margin-bottom: .75rem;
            cursor: pointer;
            transition: all .2s ease;
            text-decoration: none;
            color: inherit;
            background: #fff;
        }

        .company-option:hover {
            border-color: #4e73df;
            background: #f0f4ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(78,115,223,.15);
        }

        .company-option .company-info h6 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }

        .company-option .company-info small {
            color: #6c757d;
        }

        .company-option .arrow {
            color: #4e73df;
            font-size: 1.25rem;
            opacity: 0;
            transition: opacity .2s;
        }

        .company-option:hover .arrow {
            opacity: 1;
        }

        .role-badge {
            font-size: .7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
    </style>
</head>
<body>

    <div class="select-container">
        <div class="brand-header">
            <h2><i class="bi bi-camera-video-fill me-2"></i>CCTV Management</h2>
            <p>Select a company to continue</p>
        </div>

        <div class="select-card">
            <div class="card-header-custom">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle fs-4 text-muted"></i>
                    <div>
                        <h6 class="mb-0">Welcome, {{ Auth::user()->name }}</h6>
                        <small class="text-muted">{{ Auth::user()->email }}</small>
                    </div>
                </div>
            </div>

            <div class="card-body-custom">
                @if($companies->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-building-slash fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No companies assigned to your account.</p>
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        <i class="bi bi-building me-1"></i>
                        {{ $companies->count() }} {{ Str::plural('company', $companies->count()) }} available
                    </p>

                    @foreach($companies as $company)
                        <form method="POST" action="{{ route('company.set', $company->id) }}" class="d-block">
                            @csrf
                            <button type="submit" class="company-option w-100 text-start">
                                <div class="company-info">
                                    <h6>
                                        <i class="bi bi-building me-2 text-primary"></i>
                                        {{ $company->name }}
                                    </h6>
                                    <small>
                                        Role:
                                        <span class="badge role-badge
                                            @if($company->pivot->role === 'admin') bg-danger
                                            @elseif($company->pivot->role === 'manager') bg-warning text-dark
                                            @elseif($company->pivot->role === 'technician') bg-info text-dark
                                            @else bg-secondary
                                            @endif
                                        ">
                                            {{ ucfirst($company->pivot->role) }}
                                        </span>
                                    </small>
                                </div>
                                <span class="arrow"><i class="bi bi-arrow-right-circle-fill"></i></span>
                            </button>
                        </form>
                    @endforeach
                @endif

                <hr class="my-3">

                <div class="text-center">
                    <a href="{{ route('logout') }}" class="btn btn-outline-secondary btn-sm"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
