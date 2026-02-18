@extends('layouts.app')

@section('title', $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $customer->name }}</h4>
        <p class="text-muted mb-0">Customer Details</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- Customer Info Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Name</small>
                        <span class="fw-semibold">{{ $customer->name }}</span>
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
                        <span class="fw-semibold">{{ $customer->phone }}</span>
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
                        <span class="fw-semibold">{{ $customer->email ?? '—' }}</span>
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
                        <span class="fw-semibold">{{ $customer->gstin ?? '—' }}</span>
                    </div>
                </div>
            </div>
            @if($customer->address)
            <div class="col-12">
                <div class="d-flex align-items-start mb-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-geo-alt-fill text-secondary fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Address</small>
                        <span class="fw-semibold">{{ $customer->address }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Sites --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Sites</h6>
        <a href="{{ route('customers.sites.create', $customer) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Site
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Site Name</th>
                        <th>Address</th>
                        <th>Contact Person</th>
                        <th>Contact Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->sites as $site)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $site->name }}</td>
                        <td>{{ Str::limit($site->address, 40) }}</td>
                        <td>{{ $site->contact_person ?? '—' }}</td>
                        <td>{{ $site->contact_phone ?? '—' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.sites.edit', [$customer, $site]) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('customers.sites.destroy', [$customer, $site]) }}" method="POST"
                                      onsubmit="return confirm('Delete this site?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No sites added yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent Invoices --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Recent Invoices</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($customer->invoices ?? collect())->take(10) as $invoice)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $invoice->invoice_number }}</code></td>
                        <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td class="fw-semibold">{{ number_format($invoice->total, 2) }}</td>
                        <td>
                            @php
                                $statusColors = ['paid' => 'success', 'unpaid' => 'danger', 'partial' => 'warning'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No invoices yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent Tickets --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-headset me-2"></i>Recent Tickets</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ticket No</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($customer->tickets ?? collect())->take(10) as $ticket)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code>{{ $ticket->ticket_number }}</code></td>
                        <td>{{ Str::limit($ticket->subject, 40) }}</td>
                        <td>
                            @php
                                $priorityColors = ['low' => 'info', 'medium' => 'warning', 'high' => 'danger', 'critical' => 'dark'];
                            @endphp
                            <span class="badge bg-{{ $priorityColors[$ticket->priority] ?? 'secondary' }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $ticketStatusColors = ['open' => 'primary', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $ticketStatusColors[$ticket->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td>{{ $ticket->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No tickets yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
