@extends('layouts.app')

@section('title', $vendor->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $vendor->name }}</h4>
        <p class="text-muted mb-0">Vendor Details</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('vendors.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- Vendor Info Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-truck text-primary fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Name</small>
                        <span class="fw-semibold">{{ $vendor->name }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-telephone-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Phone</small>
                        <span class="fw-semibold">{{ $vendor->phone ?? '—' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-envelope-fill text-info fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Email</small>
                        <span class="fw-semibold">{{ $vendor->email ?? '—' }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-building text-warning fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">GSTIN</small>
                        <span class="fw-semibold">{{ $vendor->gstin ?? '—' }}</span>
                    </div>
                </div>
            </div>
            @if($vendor->address)
            <div class="col-12">
                <div class="d-flex align-items-start mb-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-geo-alt-fill text-secondary fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Address</small>
                        <span class="fw-semibold">{{ $vendor->address }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Recent Purchases --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-cart-plus me-2"></i>Recent Purchases</h6>
        <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Purchase
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Purchase No</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($vendor->purchases ?? collect())->take(10) as $purchase)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $purchase->purchase_number }}</code></td>
                        <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td>{{ $purchase->items_count ?? $purchase->items->count() }}</td>
                        <td class="fw-semibold">{{ number_format($purchase->total, 2) }}</td>
                        <td>
                            @php
                                $statusColors = ['received' => 'success', 'pending' => 'warning', 'cancelled' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$purchase->status] ?? 'secondary' }}">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No purchases yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
