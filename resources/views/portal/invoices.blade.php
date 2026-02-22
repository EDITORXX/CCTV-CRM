@extends('layouts.app')

@section('title', 'My Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">My Invoices</h4>
        <p class="text-muted mb-0">View all invoices issued to your account</p>
    </div>
</div>

{{-- Invoices List --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Site</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td><strong>{{ $invoice->invoice_number }}</strong></td>
                        <td class="text-muted">{{ $invoice->invoice_date->format('d M Y') }}</td>
                        <td>{{ $invoice->site->site_name ?? '-' }}</td>
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
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>

                    {{-- Product Details Row --}}
                    @if($invoice->items->count())
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            <div class="bg-light px-4 py-2">
                                <small class="fw-semibold text-muted d-block mb-1">Products in this invoice:</small>
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 0.85rem;">
                                        <thead>
                                            <tr class="text-muted">
                                                <th>Product</th>
                                                <th>Brand</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-center">Warranty</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoice->items as $item)
                                            <tr>
                                                <td>{{ $item->product->name ?? '-' }}</td>
                                                <td class="text-muted">{{ $item->product->brand ?? '-' }}</td>
                                                <td class="text-center">{{ $item->qty }}</td>
                                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-center">
                                                    @if($item->warranty_months > 0)
                                                        <span class="badge bg-success bg-opacity-75">
                                                            {{ $item->warranty_months >= 12 ? intval($item->warranty_months / 12) . ' Year' : $item->warranty_months . ' Mo' }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-semibold">₹{{ number_format($item->total, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif

                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            No invoices found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $invoices->links() }}
</div>

{{-- Products Catalog --}}
@if(isset($products) && $products->count())
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-box-seam me-1"></i> Available Products & Pricing
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Warranty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            @if($product->model_number)
                                <small class="text-muted d-block">{{ $product->model_number }}</small>
                            @endif
                        </td>
                        <td>{{ $product->brand ?? '-' }}</td>
                        <td>{{ ucfirst(str_replace('_', '/', $product->category ?? '-')) }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($product->sale_price, 2) }}</td>
                        <td class="text-center">
                            @if($product->warranty_months > 0)
                                <span class="badge bg-success">
                                    {{ $product->warranty_months >= 12 ? intval($product->warranty_months / 12) . ' Year' : $product->warranty_months . ' Month' }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
