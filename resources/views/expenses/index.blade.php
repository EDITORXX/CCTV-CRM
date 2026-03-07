@extends('layouts.app')

@section('title', 'Expenses')

@section('styles')
<style>
    @media (max-width: 767.98px) {
        .expense-mobile-card .expense-amount {
            font-size: 1rem;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Expenses</h4>
        <p class="text-muted mb-0">Regular and site expenses in one place</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-tags me-1"></i><span class="d-none d-sm-inline"> Categories</span>
        </a>
        <a href="{{ route('expenses.record') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline"> Record</span> Expense
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<ul class="nav nav-tabs mb-4" id="expensesTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="regular-tab" data-bs-toggle="tab" data-bs-target="#regular" type="button" role="tab">
            <span class="d-none d-sm-inline">1. Regular</span><span class="d-sm-none">Regular</span> Expenses
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab">
            <span class="d-none d-sm-inline">2. Site</span><span class="d-sm-none">Site</span> Expenses
        </button>
    </li>
</ul>

<div class="tab-content" id="expensesTabContent">
    {{-- Regular Expenses --}}
    <div class="tab-pane fade show active" id="regular" role="tabpanel">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('expenses.index') }}">
                    <input type="hidden" name="site_page" value="{{ request('site_page', 1) }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Category</label>
                            <select class="form-select form-select-sm" name="expense_category_id">
                                <option value="">All</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">From</label>
                            <input type="date" class="form-control form-control-sm" name="reg_from" value="{{ request('reg_from') }}">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">To</label>
                            <input type="date" class="form-control form-control-sm" name="reg_to" value="{{ request('reg_to') }}">
                        </div>
                        <div class="col-6 col-md-auto">
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100 w-md-auto">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Desktop table --}}
        <div class="card border-0 shadow-sm d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($regularExpenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                <td><span class="badge bg-secondary">{{ $expense->expenseCategory->name ?? '-' }}</span></td>
                                <td>{{ Str::limit($expense->description, 40) ?: '—' }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->creator->name ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('regular-expenses.edit', $expense) }}" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('regular-expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No regular expenses.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="d-md-none">
            @forelse($regularExpenses as $expense)
            <div class="card border-0 shadow-sm mb-2 expense-mobile-card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="badge bg-secondary mb-1">{{ $expense->expenseCategory->name ?? '-' }}</span>
                            <div class="small text-muted">{{ $expense->expense_date->format('d M Y') }}</div>
                        </div>
                        <span class="fw-bold text-dark expense-amount">₹{{ number_format($expense->amount, 2) }}</span>
                    </div>
                    @if($expense->description)
                        <p class="small text-muted mb-2">{{ Str::limit($expense->description, 60) }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">By: {{ $expense->creator->name ?? '-' }}</small>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('regular-expenses.edit', $expense) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('regular-expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                <p>No regular expenses.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-2">{{ $regularExpenses->withQueryString()->links() }}</div>
    </div>

    {{-- Site Expenses --}}
    <div class="tab-pane fade" id="site" role="tabpanel">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('expenses.index') }}">
                    <input type="hidden" name="reg_page" value="{{ request('reg_page', 1) }}">
                    <input type="hidden" name="expense_category_id" value="{{ request('expense_category_id') }}">
                    <input type="hidden" name="reg_from" value="{{ request('reg_from') }}">
                    <input type="hidden" name="reg_to" value="{{ request('reg_to') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label small mb-1">Customer</label>
                            <select class="form-select form-select-sm" name="customer_id">
                                <option value="">All</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">From</label>
                            <input type="date" class="form-control form-control-sm" name="site_from" value="{{ request('site_from') }}">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">To</label>
                            <input type="date" class="form-control form-control-sm" name="site_to" value="{{ request('site_to') }}">
                        </div>
                        <div class="col-12 col-md-auto">
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100 w-md-auto">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Desktop table --}}
        <div class="card border-0 shadow-sm d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Site</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Technician</th>
                                <th>Recorded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siteExpenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                <td>{{ $expense->customer->name ?? '-' }}</td>
                                <td>{{ $expense->site->site_name ?? '-' }}</td>
                                <td>{{ Str::limit($expense->description, 40) }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->technician_name ?? '-' }}</td>
                                <td>{{ $expense->creator->name ?? '-' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('site-expenses.edit', $expense) }}" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('site-expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No site expenses.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="d-md-none">
            @forelse($siteExpenses as $expense)
            <div class="card border-0 shadow-sm mb-2 expense-mobile-card">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $expense->customer->name ?? '-' }}</div>
                            <div class="small text-muted">{{ $expense->site->site_name ?? '-' }} &middot; {{ $expense->expense_date->format('d M Y') }}</div>
                        </div>
                        <span class="fw-bold text-dark expense-amount ms-2 flex-shrink-0">₹{{ number_format($expense->amount, 2) }}</span>
                    </div>
                    @if($expense->description)
                        <p class="small text-muted mb-2">{{ Str::limit($expense->description, 60) }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            @if($expense->technician_name)
                                <i class="bi bi-person me-1"></i>{{ $expense->technician_name }}
                            @endif
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('site-expenses.edit', $expense) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('site-expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                <p>No site expenses.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-2">{{ $siteExpenses->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
