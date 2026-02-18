@extends('layouts.app')

@section('title', 'Serial: ' . $serial->serial_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-upc-scan me-2"></i>{{ $serial->serial_number }}</h4>
        <p class="text-muted mb-0">{{ $serial->product->name ?? 'Unknown Product' }} — Full lifecycle details</p>
    </div>
    <a href="{{ route('serials.search') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Search
    </a>
</div>

<div class="row g-4">
    {{-- Serial Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-upc-scan me-1"></i> Serial Details
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="110">Serial #</td>
                        <td><code class="fw-bold fs-6">{{ $serial->serial_number }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Product</td>
                        <td class="fw-semibold">{{ $serial->product->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @switch($serial->status)
                                @case('in_stock')
                                    <span class="badge bg-success">In Stock</span>
                                    @break
                                @case('sold')
                                    <span class="badge bg-primary">Sold</span>
                                    @break
                                @case('installed')
                                    <span class="badge bg-info">Installed</span>
                                    @break
                                @case('defective')
                                    <span class="badge bg-danger">Defective</span>
                                    @break
                                @case('rma')
                                    <span class="badge bg-warning text-dark">RMA</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($serial->status) }}</span>
                            @endswitch
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Purchase Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-cart-plus me-1"></i> Purchase Info
            </div>
            <div class="card-body">
                @if($serial->purchaseItem && $serial->purchaseItem->purchase)
                    @php $purchase = $serial->purchaseItem->purchase; @endphp
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" width="110">Vendor</td>
                            <td class="fw-semibold">{{ $purchase->vendor->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bill #</td>
                            <td><code>{{ $purchase->bill_number }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date</td>
                            <td>{{ \Carbon\Carbon::parse($purchase->bill_date)->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Unit Price</td>
                            <td>₹{{ number_format($serial->purchaseItem->unit_price, 2) }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted text-center mb-0">No purchase information available.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Sale Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt me-1"></i> Sale Info
            </div>
            <div class="card-body">
                @if($serial->invoiceItem && $serial->invoiceItem->invoice)
                    @php $invoice = $serial->invoiceItem->invoice; @endphp
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" width="110">Customer</td>
                            <td class="fw-semibold">{{ $invoice->customer->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Invoice #</td>
                            <td>
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Date</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted text-center mb-0">Not sold yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    {{-- Installation --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-geo-alt me-1"></i> Installation
            </div>
            <div class="card-body">
                @if($serial->site)
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" width="110">Site Name</td>
                            <td class="fw-semibold">{{ $serial->site->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Address</td>
                            <td>{{ $serial->site->address ?? '—' }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted text-center mb-0">Not installed at any site.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Warranty --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Warranty
            </div>
            <div class="card-body">
                @if($serial->warranty)
                    @php
                        $w = $serial->warranty;
                        $endDate = \Carbon\Carbon::parse($w->end_date);
                        $daysLeft = now()->diffInDays($endDate, false);
                    @endphp
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" width="110">Start</td>
                            <td>{{ \Carbon\Carbon::parse($w->start_date)->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">End</td>
                            <td>{{ $endDate->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @switch($w->status)
                                    @case('active')
                                        <span class="badge bg-success">Active</span>
                                        @break
                                    @case('expired')
                                        <span class="badge bg-danger">Expired</span>
                                        @break
                                    @case('replaced')
                                        <span class="badge bg-warning text-dark">Replaced</span>
                                        @break
                                    @case('rma')
                                        <span class="badge bg-info">RMA</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($w->status) }}</span>
                                @endswitch
                            </td>
                        </tr>
                        @if($w->status === 'active')
                        <tr>
                            <td class="text-muted">Days Left</td>
                            <td class="{{ $daysLeft <= 30 ? 'text-warning fw-bold' : 'text-success fw-bold' }}">
                                {{ max(0, $daysLeft) }} days
                            </td>
                        </tr>
                        @endif
                    </table>
                @else
                    <p class="text-muted text-center mb-0">No warranty record.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Service History --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-headset me-1"></i> Service History
            </div>
            <div class="card-body">
                @if(isset($serial->tickets) && $serial->tickets->count())
                    <div class="list-group list-group-flush">
                        @foreach($serial->tickets as $ticket)
                        <a href="{{ route('tickets.show', $ticket) }}" class="list-group-item list-group-item-action px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $ticket->ticket_number }}</div>
                                    <small class="text-muted">{{ $ticket->complaint_type }}</small>
                                </div>
                                <div>
                                    @switch($ticket->status)
                                        @case('open')
                                            <span class="badge bg-warning text-dark">Open</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                            @break
                                        @case('resolved')
                                            <span class="badge bg-success">Resolved</span>
                                            @break
                                        @case('closed')
                                            <span class="badge bg-secondary">Closed</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No service tickets for this serial.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
