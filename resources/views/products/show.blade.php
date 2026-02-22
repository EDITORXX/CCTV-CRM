@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $product->name }}</h4>
        <p class="text-muted mb-0">Product Details</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- Product Info Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4">
                <small class="text-muted d-block">Category</small>
                <span class="badge bg-secondary fs-6">{{ $product->category }}</span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Brand</small>
                <span class="fw-semibold">{{ $product->brand ?? '—' }}</span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Model Number</small>
                <span class="fw-semibold">{{ $product->model_number ?? '—' }}</span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">HSN/SAC Code</small>
                <span class="fw-semibold">{{ $product->hsn_sac ?? '—' }}</span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Unit</small>
                <span class="fw-semibold">{{ $product->unit == 'pcs' ? 'Pieces' : 'Meter' }}</span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Warranty</small>
                <span class="fw-semibold">{{ $product->warranty_months ? $product->warranty_months . ' months' : '—' }}</span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Sale Price</small>
                <span class="fw-bold text-success fs-5">&#8377; {{ number_format($product->sale_price, 2) }}</span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block">Track Serial Numbers</small>
                @if($product->track_serial)
                    <span class="badge bg-success">Yes</span>
                @else
                    <span class="badge bg-light text-muted">No</span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Stock Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-cart-plus"></i>
                </div>
                <div>
                    <small class="text-muted">Total Purchased</small>
                    <h4 class="mb-0 fw-bold">{{ $product->total_purchased ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-cart-dash"></i>
                </div>
                <div>
                    <small class="text-muted">Total Sold</small>
                    <h4 class="mb-0 fw-bold">{{ $product->total_sold ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <small class="text-muted">Available Stock</small>
                    <h4 class="mb-0 fw-bold">{{ $product->stock_qty ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Serial Numbers (if track_serial is enabled) --}}
@if($product->track_serial)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-upc-scan me-2"></i>Serial Numbers</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="serialsTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Serial Number</th>
                        <th>Status</th>
                        <th>Purchase Ref</th>
                        <th>Invoice Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($product->serialNumbers ?? collect()) as $serial)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><code class="fs-6">{{ $serial->serial_number }}</code></td>
                        <td>
                            @php
                                $serialStatusColors = [
                                    'in_stock' => 'success',
                                    'sold' => 'primary',
                                    'warranty_replacement' => 'warning',
                                    'defective' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $serialStatusColors[$serial->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $serial->status)) }}
                            </span>
                        </td>
                        <td>{{ $serial->purchase ? $serial->purchase->purchase_number : '—' }}</td>
                        <td>{{ $serial->invoice ? $serial->invoice->invoice_number : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
@if($product->track_serial)
<script>
    $(document).ready(function() {
        $('#serialsTable').DataTable({
            paging: true,
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0] }
            ],
            language: {
                emptyTable: 'No serial numbers recorded.'
            }
        });
    });
</script>
@endif
@endsection
