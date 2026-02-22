@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')

{{-- Welcome --}}
<div class="mb-4">
    <h4 class="fw-bold mb-1">Welcome, {{ Auth::user()->name }}!</h4>
    <p class="text-muted mb-0">Here's a summary of your account activity.</p>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card shadow-sm border-start border-primary border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">My Invoices</div>
                    <h4 class="mb-0 fw-bold">{{ $invoiceCount ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card shadow-sm border-start border-success border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Active Warranties</div>
                    <h4 class="mb-0 fw-bold">{{ $warrantyCount ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="card stat-card shadow-sm border-start border-warning border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Open Complaints</div>
                    <h4 class="mb-0 fw-bold">{{ $complaintCount ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Recent Invoices --}}
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-receipt me-2 text-primary"></i>Recent Invoices
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices ?? [] as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td class="text-muted">{{ $invoice->created_at->format('d M Y') }}</td>
                                <td class="fw-semibold">₹{{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    <span class="badge
                                        @if($invoice->status === 'paid') bg-success
                                        @elseif($invoice->status === 'partial') bg-warning text-dark
                                        @else bg-danger
                                        @endif
                                    ">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-receipt fs-4 d-block mb-1"></i>No invoices yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Raise New Complaint
                    </a>
                    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-headset me-2"></i>View My Complaints
                    </a>
                    <a href="{{ route('warranties.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-shield-check me-2"></i>Check Warranties
                    </a>
                    <a href="{{ route('support.index') }}" class="btn btn-outline-info">
                        <i class="bi bi-life-preserver me-2"></i>Help Center & Videos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
