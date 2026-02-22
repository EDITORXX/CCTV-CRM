@extends('layouts.app')

@section('title', 'Estimate ' . $estimate->estimate_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Estimate {{ $estimate->estimate_number }}</h4>
        <p class="text-muted mb-0">{{ $estimate->estimate_date->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('estimates.pdf', $estimate) }}" class="btn btn-outline-info" target="_blank">
            <i class="bi bi-printer me-1"></i> Print PDF
        </a>
        <a href="{{ route('estimates.download', $estimate) }}" class="btn btn-outline-secondary">
            <i class="bi bi-download me-1"></i> Download
        </a>
        @if(!$estimate->isConverted())
        <a href="{{ route('estimates.edit', $estimate) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Delete this estimate?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
        </form>
        @endif
        <a href="{{ route('estimates.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-1"></i> Estimate Info</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Estimate Number</small>
                        <strong>{{ $estimate->estimate_number }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Date</small>
                        <strong>{{ $estimate->estimate_date->format('d M Y') }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Valid Until</small>
                        <strong>{{ $estimate->valid_until ? $estimate->valid_until->format('d M Y') : '-' }}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge
                            @if($estimate->status === 'draft') bg-secondary
                            @elseif($estimate->status === 'sent') bg-info
                            @elseif($estimate->status === 'accepted') bg-success
                            @elseif($estimate->status === 'rejected') bg-danger
                            @elseif($estimate->status === 'converted') bg-primary
                            @endif fs-6
                        ">{{ ucfirst($estimate->status) }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Customer</small>
                        <strong>{{ $estimate->customer->name }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Site</small>
                        <strong>{{ $estimate->site->site_name ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">GST</small>
                        <strong>{{ $estimate->is_gst ? 'Yes' : 'No' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-list-ul me-1"></i> Line Items & Stock Status</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                @if($estimate->is_gst)<th class="text-center">GST%</th>@endif
                                <th class="text-end">Total</th>
                                <th class="text-center">In Stock</th>
                                <th class="text-center">Short</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estimate->items as $i => $item)
                            @php $stock = $stockInfo[$item->id] ?? ['available' => 0, 'required' => 0, 'short' => 0]; @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    {{ $item->product->name }}
                                    @if($item->product->brand) <small class="text-muted">- {{ $item->product->brand }}</small>@endif
                                </td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                @if($estimate->is_gst)<td class="text-center">{{ $item->gst_percent }}%</td>@endif
                                <td class="text-end fw-semibold">₹{{ number_format($item->total, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $stock['available'] >= $stock['required'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $stock['available'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($stock['short'] > 0)
                                        <span class="badge bg-danger">{{ $stock['short'] }}</span>
                                    @else
                                        <span class="text-success">--</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($estimate->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Notes</small>
                {{ $estimate->notes }}
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-calculator me-1"></i> Summary</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">Subtotal</td><td class="text-end fw-semibold">₹{{ number_format($estimate->subtotal, 2) }}</td></tr>
                    @if($estimate->is_gst)
                    <tr><td class="text-muted">GST Amount</td><td class="text-end fw-semibold">₹{{ number_format($estimate->gst_amount, 2) }}</td></tr>
                    @endif
                    @if($estimate->discount > 0)
                    <tr><td class="text-muted">Discount</td><td class="text-end fw-semibold text-danger">-₹{{ number_format($estimate->discount, 2) }}</td></tr>
                    @endif
                    <tr class="border-top">
                        <td class="fw-bold fs-5">Total</td>
                        <td class="text-end fw-bold fs-5 text-success">₹{{ number_format($estimate->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($estimate->isConverted() && $estimate->convertedInvoice)
        <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
            <div class="card-body">
                <small class="text-muted d-block mb-1">Converted to Invoice</small>
                <a href="{{ route('invoices.show', $estimate->converted_invoice_id) }}" class="fw-bold text-primary">
                    {{ $estimate->convertedInvoice->invoice_number }}
                </a>
            </div>
        </div>
        @endif

        @if(!$estimate->isConverted())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-arrow-repeat me-1"></i> Actions</div>
            <div class="card-body d-grid gap-2">
                <form action="{{ route('estimates.convert', $estimate) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Convert this estimate to an invoice?')">
                        <i class="bi bi-receipt me-1"></i> Convert to Invoice
                    </button>
                </form>

                @php $hasShortage = collect($stockInfo)->contains(fn($s) => $s['short'] > 0); @endphp
                @if($hasShortage)
                <hr>
                <p class="text-muted small mb-2">Some items are out of stock. Create a purchase order for the shortage:</p>
                <form action="{{ route('estimates.purchase-order', $estimate) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <select name="vendor_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="bi bi-cart-plus me-1"></i> Create Purchase Order
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
