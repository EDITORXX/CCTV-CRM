<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Login - CCTV Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1c2e 0%, #2d3154 50%, #4e73df 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            max-width: 800px;
            width: 100%;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand-header h1 {
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .brand-header p {
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
        }
        .user-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
            overflow: hidden;
        }
        .user-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .user-card .card-body {
            padding: 20px;
        }
        .role-badge {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .role-company_admin { background: #dc3545; color: #fff; }
        .role-manager { background: #0d6efd; color: #fff; }
        .role-accountant { background: #6f42c1; color: #fff; }
        .role-technician { background: #fd7e14; color: #fff; }
        .role-customer { background: #20c997; color: #fff; }
        .role-user { background: #6c757d; color: #fff; }
        .user-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            flex-shrink: 0;
        }
        .icon-company_admin { background: #dc3545; }
        .icon-manager { background: #0d6efd; }
        .icon-accountant { background: #6f42c1; }
        .icon-technician { background: #fd7e14; }
        .icon-customer { background: #20c997; }
        .icon-user { background: #6c757d; }
        .user-info h6 {
            margin: 0;
            font-weight: 600;
            font-size: 1rem;
        }
        .user-info small {
            color: #6c757d;
            font-size: 0.8rem;
        }
        .login-link {
            text-align: center;
            margin-top: 25px;
        }
        .login-link a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .login-link a:hover {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-header">
            <h1><i class="bi bi-camera-video"></i> CCTV Management</h1>
            <p>Click on any user to login instantly</p>
        </div>

        <div class="row g-3">
            @forelse($users as $user)
            @php
                $firstCompany = $user->companies->first();
                $role = $firstCompany ? $firstCompany->pivot->role : 'user';
                $companyName = $firstCompany ? $firstCompany->name : 'No company';
            @endphp
            <div class="col-md-6">
                <form method="POST" action="{{ route('quick-login.do', $user->id) }}">
                    @csrf
                    <button type="submit" class="btn p-0 w-100 text-start">
                        <div class="card user-card">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="user-icon icon-{{ $role }}">
                                    @if($role === 'company_admin')
                                        <i class="bi bi-shield-lock"></i>
                                    @elseif($role === 'manager')
                                        <i class="bi bi-person-badge"></i>
                                    @elseif($role === 'accountant')
                                        <i class="bi bi-calculator"></i>
                                    @elseif($role === 'technician')
                                        <i class="bi bi-tools"></i>
                                    @elseif($role === 'customer')
                                        <i class="bi bi-person"></i>
                                    @else
                                        <i class="bi bi-person"></i>
                                    @endif
                                </div>
                                <div class="user-info flex-grow-1">
                                    <h6>{{ $user->name }}</h6>
                                    <small>{{ $user->email }}</small>
                                </div>
                                <div>
                                    <span class="role-badge role-{{ $role }}">
                                        {{ str_replace('_', ' ', $role) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-light text-muted text-center py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-building"></i> {{ $companyName }}
                            </div>
                        </div>
                    </button>
                </form>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <p class="text-muted mb-0">No demo users available. Use email & password login below.</p>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <div class="login-link">
            <a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i> Use Email & Password Login Instead</a>
        </div>
    </div>
</body>
</html>
