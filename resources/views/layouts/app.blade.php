<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'CCTV Management') }}</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1a1c2e;
            --sidebar-hover: #2d2f45;
            --sidebar-active: #4e73df;
            --topbar-height: 60px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6f9;
            margin: 0;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: #fff;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-brand h5 {
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: .3px;
        }

        .sidebar-brand small {
            opacity: .55;
            font-size: .75rem;
        }

        .sidebar-nav {
            list-style: none;
            padding: .75rem 0;
            margin: 0;
            flex: 1;
        }

        .sidebar-nav .nav-label {
            padding: .5rem 1.5rem;
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: .4;
            margin-top: .5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 1.5rem;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            border-radius: 0;
            transition: all .2s ease;
        }

        .sidebar-nav a:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-nav a.active {
            background: var(--sidebar-active);
            color: #fff;
        }

        .sidebar-nav a i {
            font-size: 1.15rem;
            width: 1.25rem;
            text-align: center;
        }

        /* ── Main content ── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top navbar ── */
        .topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e3e6ec;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .topbar .page-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: #333;
        }

        .topbar .btn-hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            padding: .25rem .5rem;
            color: #333;
        }

        .content-body {
            padding: 1.5rem;
            flex: 1;
        }

        /* ── Sidebar overlay (mobile) ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 1035;
        }

        /* ── Responsive ── */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-overlay.show {
                display: block;
            }
            .main-wrapper {
                margin-left: 0;
            }
            .topbar .btn-hamburger {
                display: inline-block;
            }
        }

        /* ── Stat cards ── */
        .stat-card {
            border: none;
            border-radius: .75rem;
            transition: transform .15s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        /* ── Toast container ── */
        .toast-container-fixed {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1090;
        }

        /* ── DataTables tweaks ── */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: .375rem;
        }

        /* ── Scrollbar ── */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 2px; }
    </style>

    @yield('styles')
</head>
<body>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h5><i class="bi bi-camera-video-fill me-2"></i>CCTV Mgmt</h5>
            @if(isset($currentCompany))
                <small>{{ $currentCompany->name }}</small>
            @endif
        </div>

        @php
            $userRole = null;
            if(auth()->check() && session('current_company_id')) {
                $companyPivot = auth()->user()->companies()->where('companies.id', session('current_company_id'))->first();
                $userRole = $companyPivot ? $companyPivot->pivot->role : null;
            }
        @endphp

        <ul class="sidebar-nav">
            <li class="nav-label">Main</li>
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            @if($userRole === 'customer')
                {{-- Customer-only sidebar --}}
                <li class="nav-label">My Account</li>
                <li>
                    <a href="{{ route('portal.invoices') }}" class="{{ request()->routeIs('portal.invoices*') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i> My Invoices
                    </a>
                </li>
                <li>
                    <a href="{{ route('portal.warranties') }}" class="{{ request()->routeIs('portal.warranties') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> My Warranties
                    </a>
                </li>
                <li>
                    <a href="{{ route('portal.payments') }}" class="{{ request()->routeIs('portal.payments*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i> My Payments
                    </a>
                </li>
                <li>
                    <a href="{{ route('portal.complaints') }}" class="{{ request()->routeIs('portal.complaints*') ? 'active' : '' }}">
                        <i class="bi bi-headset"></i> Support Tickets
                    </a>
                </li>
                <li>
                    <a href="{{ route('portal.profile') }}" class="{{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                        <i class="bi bi-person-circle"></i> My Profile
                    </a>
                </li>
                <li>
                    <a href="{{ route('support.index') }}" class="{{ request()->routeIs('support.*') ? 'active' : '' }}">
                        <i class="bi bi-life-preserver"></i> Help Center
                    </a>
                </li>
            @elseif($userRole === 'technician')
                {{-- Technician sidebar --}}
                <li class="nav-label">Work</li>
                <li>
                    <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                        <i class="bi bi-headset"></i> Service Tickets
                    </a>
                </li>
                <li>
                    <a href="{{ route('site-expenses.create') }}" class="{{ request()->routeIs('site-expenses.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i> Record Expense
                    </a>
                </li>
                <li>
                    <a href="{{ route('support.index') }}" class="{{ request()->routeIs('support.*') ? 'active' : '' }}">
                        <i class="bi bi-life-preserver"></i> Help Center
                    </a>
                </li>
            @else
                {{-- Admin/Manager/Accountant sidebar --}}
                @if($userRole && !in_array($userRole, ['technician']))
                <li class="nav-label">Operations</li>
                <li>
                    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Customers
                    </a>
                </li>
                <li>
                    <a href="{{ route('serials.search') }}" class="{{ request()->routeIs('serials.*') ? 'active' : '' }}">
                        <i class="bi bi-upc-scan"></i> Serial Search
                    </a>
                </li>
                <li>
                    <a href="{{ route('vendors.index') }}" class="{{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                        <i class="bi bi-truck"></i> Vendors
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i> Products
                    </a>
                </li>
                @if($userRole && in_array($userRole, ['company_admin', 'manager', 'accountant']))
                <li>
                    <a href="{{ route('site-expenses.index') }}" class="{{ request()->routeIs('site-expenses.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i> Site Expenses
                    </a>
                </li>
                @endif
                @endif

                <li class="nav-label">Billing</li>
                <li>
                    <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                        <i class="bi bi-cart-plus"></i> Purchases
                    </a>
                </li>
                <li>
                    <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i> Invoices
                    </a>
                </li>
                @if($userRole && in_array($userRole, ['company_admin', 'manager', 'accountant']))
                <li>
                    <a href="{{ route('estimates.index') }}" class="{{ request()->routeIs('estimates.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Estimates
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer-payments.index') }}" class="{{ request()->routeIs('customer-payments.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card-2-front"></i> Payment Approvals
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer-advances.index') }}" class="{{ request()->routeIs('customer-advances.*') ? 'active' : '' }}">
                        <i class="bi bi-wallet2"></i> Customer Advances
                    </a>
                </li>
                @endif

                <li class="nav-label">Support</li>
                <li>
                    <a href="{{ route('warranties.index') }}" class="{{ request()->routeIs('warranties.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> Warranties
                    </a>
                </li>
                <li>
                    <a href="{{ route('tickets.index') }}" class="{{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                        <i class="bi bi-headset"></i> Service Tickets
                    </a>
                </li>
                <li>
                    <a href="{{ route('support-articles.index') }}" class="{{ request()->routeIs('support-articles.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Knowledge Base
                    </a>
                </li>
                <li>
                    <a href="{{ route('support-videos.index') }}" class="{{ request()->routeIs('support-videos.*') ? 'active' : '' }}">
                        <i class="bi bi-camera-video"></i> Support Videos
                    </a>
                </li>
                <li>
                    <a href="{{ route('support.index') }}" class="{{ request()->routeIs('support.*') ? 'active' : '' }}">
                        <i class="bi bi-life-preserver"></i> Help Center
                    </a>
                </li>

                @if($userRole && in_array($userRole, ['company_admin', 'manager']))
                <li class="nav-label">Administration</li>
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i> Users
                    </a>
                </li>
                <li>
                    <a href="{{ route('company.settings') }}" class="{{ request()->routeIs('company.settings*') ? 'active' : '' }}">
                        <i class="bi bi-building-gear"></i> Company Settings
                    </a>
                </li>
                @endif
            @endif
        </ul>
    </aside>

    <!-- Main wrapper -->
    <div class="main-wrapper">
        <!-- Top navbar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn-hamburger" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                @auth
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('company.select') }}">
                                <i class="bi bi-building me-2"></i>Switch Company
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </header>

        <!-- Flash messages -->
        <div class="toast-container-fixed" id="toastContainer">
            @if(session('success'))
            <div class="toast align-items-center text-bg-success border-0 show" role="alert" data-bs-autohide="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            @endif
            @if(session('error'))
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            @endif
            @if(session('warning'))
            <div class="toast align-items-center text-bg-warning border-0 show" role="alert" data-bs-autohide="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-exclamation-circle me-2"></i>{{ session('warning') }}</div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            @endif
            @if(session('info'))
            <div class="toast align-items-center text-bg-info border-0 show" role="alert" data-bs-autohide="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body"><i class="bi bi-info-circle me-2"></i>{{ session('info') }}</div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
            @endif
        </div>

        <!-- Validation errors -->
        @if($errors->any())
        <div class="content-body pb-0">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle me-1"></i> Please fix the following:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        <!-- Page content -->
        <main class="content-body">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Sidebar toggle (mobile)
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle  = document.getElementById('sidebarToggle');

        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        // Auto-initialize Bootstrap toasts
        document.querySelectorAll('.toast.show').forEach(el => {
            new bootstrap.Toast(el).show();
        });
    </script>

    @yield('scripts')
</body>
</html>
