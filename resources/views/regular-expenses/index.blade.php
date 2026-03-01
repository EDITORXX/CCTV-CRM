@extends('layouts.app')

@section('title', 'Regular Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Regular Expenses</h4>
        <p class="text-muted mb-0">Company daily expenses (not linked to any site)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expense-categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-tags me-1"></i> Categories
        </a>
        <a href="{{ route('regular-expenses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Expense
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('regular-expenses.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Category</label>
                <select class="form-select form-select-sm" name="expense_category_id">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">From</label>
                <input type="date" class="form-control form-control-sm" name="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">To</label>
                <input type="date" class="form-control form-control-sm" name="to_date" value="{{ request('to_date') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('regular-expenses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
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
                    @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('d M Y') }}</td>
                        <td><span class="badge bg-secondary">{{ $expense->expenseCategory->name ?? '-' }}</span></td>
                        <td>{{ Str::limit($expense->description, 40) ?: '—' }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->creator->name ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('regular-expenses.edit', $expense) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('regular-expenses.destroy', $expense) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                            No regular expenses recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $expenses->links() }}
</div>
@endsection
