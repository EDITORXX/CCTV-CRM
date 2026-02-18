@extends('layouts.app')

@section('title', 'Purchase Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Purchase: {{ $purchase->bill_number }}</h4>
        <p class="text-muted mb-0">Bill from {{ $purchase->vendor->name ?? 'Unknown Vendor' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-1"></i> Bill Info
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="120">Vendor</td>
                        <td class="fw-semibold">{{ $purchase->vendor->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Bill Number</td>
                        <td><code>{{ $purchase->bill_number }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Bill Date</td>
                        <td>{{ \Carbon\Carbon::parse($purchase->bill_date)->format('d M Y') }}</td>
                    </tr>
                    @if($purchase->notes)
                    <tr>
                        <td class="text-muted">Notes</td>
                        <td>{{ $purchase->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-list-ul me-1"></i> Items
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th width="60">Qty</th>
                                <th width="110">Unit Price</th>
                                <th width="70">GST%</th>
                                <th width="120">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotal = 0; $totalGst = 0; @endphp
                            @forelse($purchase->items as $item)
                            @php
                                $lineBase = $item->quantity * $item->unit_price;
                                $lineGst = $lineBase * ($item->gst_percent / 100);
                                $lineTotal = $lineBase + $lineGst;
                                $subtotal += $lineBase;
                                $totalGst += $lineGst;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name ?? '—' }}</div>
                                    @if($item->serialNumbers && $item->serialNumbers->count())
                                        <div class="mt-1">
                                            @foreach($item->serialNumbers as $sn)
                                                <span class="badge bg-secondary me-1 mb-1">{{ $sn->serial_number }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ $item->gst_percent }}%</td>
                                <td class="fw-semibold">₹{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No items</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end">Subtotal:</td>
                                <td class="fw-semibold">₹{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">GST:</td>
                                <td class="fw-semibold">₹{{ number_format($totalGst, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Grand Total:</td>
                                <td class="fw-bold text-success fs-5">₹{{ number_format($subtotal + $totalGst, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
