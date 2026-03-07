@extends('layouts.app')

@section('title', 'Customer Advances')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Customer Advances</h4>
        <p class="text-muted mb-0">Record advance received from customers; adjust later in invoices</p>
    </div>
    <a href="{{ route('customer-advances.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline"> Record</span> Advance
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-8 col-md-4">
                <label for="customer_id" class="form-label small mb-1">Filter by Customer</label>
                <select class="form-select form-select-sm" id="customer_id" name="customer_id">
                    <option value="">— All Customers —</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-4 col-md-2">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                    @if(request('customer_id'))
                        <a href="{{ route('customer-advances.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Desktop table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Remaining</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $adv)
                    <tr>
                        <td><code>{{ $adv->receipt_number }}</code></td>
                        <td>
                            <strong>{{ $adv->customer->name ?? '-' }}</strong>
                            @if($adv->customer->phone)<small class="text-muted d-block">{{ $adv->customer->phone }}</small>@endif
                        </td>
                        <td class="fw-semibold">₹{{ number_format($adv->amount, 2) }}</td>
                        <td>{{ $adv->payment_date->format('d M Y') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $adv->payment_method)) }}</td>
                        <td>
                            @php $rem = $adv->amount - $adv->allocations->sum('amount'); @endphp
                            <span class="{{ $rem > 0 ? 'text-success fw-semibold' : 'text-muted' }}">₹{{ number_format($rem, 2) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('customer-advances.show', $adv) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            <a href="{{ route('customer-advances.receipt', $adv) }}" class="btn btn-sm btn-outline-dark" target="_blank" title="Print receipt">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="{{ route('customer-advances.download', $adv) }}" class="btn btn-sm btn-outline-secondary" title="Download PDF">
                                <i class="bi bi-download"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No advances recorded yet. <a href="{{ route('customer-advances.create') }}">Record first advance</a></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($advances->hasPages())
        <div class="card-footer bg-white border-0">{{ $advances->links() }}</div>
    @endif
</div>

{{-- Mobile card view --}}
<div class="d-md-none">
    @forelse($advances as $adv)
    @php $rem = $adv->amount - $adv->allocations->sum('amount'); @endphp
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="min-w-0">
                    <div class="fw-bold">{{ $adv->customer->name ?? '-' }}</div>
                    <code class="small">{{ $adv->receipt_number }}</code>
                </div>
                <span class="fw-bold text-dark ms-2 flex-shrink-0 fs-6">₹{{ number_format($adv->amount, 2) }}</span>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="badge bg-light text-dark border" style="font-size:.7rem;">{{ ucfirst(str_replace('_', ' ', $adv->payment_method)) }}</span>
                <span class="badge {{ $rem > 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}" style="font-size:.7rem;">
                    Remaining: ₹{{ number_format($rem, 2) }}
                </span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $adv->payment_date->format('d M Y') }}</small>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('customer-advances.show', $adv) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('customer-advances.receipt', $adv) }}" class="btn btn-outline-dark btn-sm" target="_blank"><i class="bi bi-printer"></i></a>
                    <a href="{{ route('customer-advances.download', $adv) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i></a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
        <p>No advances recorded yet. <a href="{{ route('customer-advances.create') }}">Record first advance</a></p>
    </div>
    @endforelse
    @if($advances->hasPages())
        <div class="mt-2">{{ $advances->links() }}</div>
    @endif
</div>
@endsection
