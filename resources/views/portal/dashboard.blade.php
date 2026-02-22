@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')

<div class="mb-4">
    <h4 class="fw-bold mb-1">Welcome, {{ $customer->name }}!</h4>
    <p class="text-muted mb-0">Here's a summary of your account activity.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm border-start border-primary border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Total Invoices</div>
                    <h4 class="mb-0 fw-bold">{{ $stats['total_invoices'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm border-start border-warning border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Pending Invoices</div>
                    <h4 class="mb-0 fw-bold">{{ $stats['pending_invoices'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm border-start border-success border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Active Warranties</div>
                    <h4 class="mb-0 fw-bold">{{ $stats['active_warranties'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card stat-card shadow-sm border-start border-danger border-4">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Open Complaints</div>
                    <h4 class="mb-0 fw-bold">{{ $stats['open_complaints'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>Recent Invoices</h6>
                <a href="{{ route('portal.invoices') }}" class="btn btn-sm btn-outline-primary">View All</a>
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
                            @forelse($recentInvoices as $invoice)
                            <tr>
                                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                <td class="text-muted">{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td class="fw-semibold">₹{{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    <span class="badge
                                        @if($invoice->status === 'paid') bg-success
                                        @elseif($invoice->status === 'sent') bg-info
                                        @elseif($invoice->status === 'draft') bg-secondary
                                        @else bg-danger @endif
                                    ">{{ ucfirst($invoice->status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('portal.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
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

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-lightning-fill me-2 text-warning"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('portal.complaints') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Raise New Complaint
                    </a>
                    <a href="{{ route('portal.warranties') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-shield-check me-2"></i>Check Warranties
                    </a>
                    <a href="{{ route('portal.profile') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-person-circle me-2"></i>My Profile
                    </a>
                    <a href="{{ route('support.index') }}" class="btn btn-outline-info">
                        <i class="bi bi-life-preserver me-2"></i>Help Center
                    </a>
                </div>
            </div>
        </div>

        @if($recentTickets->count())
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-headset me-2 text-danger"></i>Recent Tickets</h6>
            </div>
            <div class="list-group list-group-flush">
                @foreach($recentTickets as $ticket)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <strong class="small">{{ $ticket->ticket_number }}</strong>
                        <span class="badge
                            @if($ticket->status === 'open') bg-danger
                            @elseif($ticket->status === 'in_progress') bg-warning text-dark
                            @elseif($ticket->status === 'resolved') bg-success
                            @else bg-secondary @endif
                        ">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                    </div>
                    <small class="text-muted">{{ Str::limit($ticket->description, 60) }}</small>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
