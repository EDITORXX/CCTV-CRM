@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Invoice {{ $invoice->invoice_number }}</h4>
        <p class="text-muted mb-0">{{ $invoice->invoice_date->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($invoice->status !== 'paid')
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payNowModal">
            <i class="bi bi-qr-code me-1"></i> Pay Now
        </button>
        @endif
        <a href="{{ route('portal.invoices') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-1"></i> Invoice Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Invoice Number</small>
                        <strong>{{ $invoice->invoice_number }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Date</small>
                        <strong>{{ $invoice->invoice_date->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge
                            @if($invoice->status === 'paid') bg-success
                            @elseif($invoice->status === 'sent') bg-info
                            @elseif($invoice->status === 'draft') bg-secondary
                            @else bg-danger @endif fs-6
                        ">{{ ucfirst($invoice->status) }}</span>
                    </div>
                    @if($invoice->site)
                    <div class="col-md-4">
                        <small class="text-muted d-block">Site</small>
                        <strong>{{ $invoice->site->site_name }}</strong>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <small class="text-muted d-block">GST Invoice</small>
                        <strong>{{ $invoice->is_gst ? 'Yes' : 'No' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-list-ul me-1"></i> Line Items
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                @if($invoice->is_gst)
                                <th class="text-center">GST%</th>
                                @endif
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    {{ $item->product->name }}
                                    @if($item->product->brand)
                                        <small class="text-muted">- {{ $item->product->brand }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                @if($invoice->is_gst)
                                <td class="text-center">{{ $item->gst_percent }}%</td>
                                @endif
                                <td class="text-end fw-semibold">₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($invoice->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Notes</small>
                {{ $invoice->notes }}
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calculator me-1"></i> Summary
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted">Subtotal</td>
                        <td class="text-end fw-semibold">₹{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->is_gst)
                    <tr>
                        <td class="text-muted">GST Amount</td>
                        <td class="text-end fw-semibold">₹{{ number_format($invoice->gst_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->discount > 0)
                    <tr>
                        <td class="text-muted">Discount</td>
                        <td class="text-end fw-semibold text-danger">-₹{{ number_format($invoice->discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <td class="fw-bold fs-5">Total</td>
                        <td class="text-end fw-bold fs-5 text-success">₹{{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($invoice->payments->count())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-cash-stack me-1"></i> Payment History
            </div>
            <div class="list-group list-group-flush">
                @foreach($invoice->payments as $payment)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <strong>₹{{ number_format($payment->amount, 2) }}</strong>
                        <small class="text-muted">{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '' }}</small>
                    </div>
                    <small class="text-muted">{{ ucfirst($payment->payment_method ?? '') }}</small>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($invoice->status !== 'paid')
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#payNowModal">
                <i class="bi bi-qr-code me-2"></i> Pay Now
            </button>
            <a href="{{ route('portal.payments') }}" class="btn btn-outline-primary">
                <i class="bi bi-upload me-2"></i> Upload Payment Screenshot
            </a>
        </div>
        @endif
    </div>
</div>

{{-- Pay Now QR Modal --}}
@if($invoice->status !== 'paid')
<div class="modal fade" id="payNowModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-qr-code me-2"></i>Scan & Pay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                @if(isset($company) && $company->payment_qr_path)
                    <img src="{{ asset('storage/' . $company->payment_qr_path) }}" alt="Payment QR Code"
                         class="img-fluid rounded mb-3" style="max-height: 350px;">
                    <div class="alert alert-info mb-3">
                        <strong>Invoice:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Amount Due:</strong> ₹{{ number_format($invoice->total - $invoice->payments->sum('amount'), 2) }}
                    </div>
                    <p class="text-muted small">Scan this QR code to make payment. After payment, upload screenshot in <a href="{{ route('portal.payments') }}"><strong>My Payments</strong></a> for confirmation.</p>
                @else
                    <div class="py-4 text-muted">
                        <i class="bi bi-qr-code fs-1 d-block mb-2"></i>
                        <p>Payment QR code is not available yet. Please contact support.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('portal.payments') }}" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i> Upload Screenshot
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
