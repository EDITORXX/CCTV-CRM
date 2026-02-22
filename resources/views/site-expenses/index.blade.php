@extends('layouts.app')

@section('title', 'Site Expenses')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Site Expenses</h4>
        <p class="text-muted mb-0">Track expenses recorded for customer sites</p>
    </div>
    <a href="{{ route('site-expenses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Expense
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('site-expenses.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Filter by Customer</label>
                <select class="form-select form-select-sm" name="customer_id" id="filterCustomer">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="{{ route('site-expenses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="expensesTable">
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
                    @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $expense->expense_date->format('d M Y') }}</td>
                        <td>{{ $expense->customer->name ?? '-' }}</td>
                        <td>{{ $expense->site->site_name ?? '-' }}</td>
                        <td>{{ Str::limit($expense->description, 50) }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ $expense->technician_name ?? '-' }}</td>
                        <td>{{ $expense->creator->name ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('site-expenses.edit', $expense) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('site-expenses.destroy', $expense) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this expense?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                            No site expenses recorded yet.
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

@section('scripts')
<script>
$(document).ready(function() {
    @if($expenses->count() > 0)
    $('#expensesTable').DataTable({
        paging: false,
        info: false,
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });
    @endif
});
</script>
@endsection
