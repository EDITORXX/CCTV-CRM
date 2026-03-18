@extends('layouts.app')

@section('title', 'Products & Services')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Products & Services</h4>
        <p class="text-muted mb-0">Manage inventory and service catalog</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($tab === 'services')
            <a href="{{ route('products.create', ['type' => 'service']) }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Service
            </a>
        @else
            <a href="{{ route('products.import') }}" class="btn btn-outline-primary btn-sm d-none d-md-inline-flex">
                <i class="bi bi-file-earmark-excel me-1"></i> Import
            </a>
            <a href="{{ route('products.bulk-create') }}" class="btn btn-outline-secondary btn-sm d-none d-md-inline-flex">
                <i class="bi bi-grid-3x3-gap me-1"></i> Add Multiple
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
        @endif
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'products' ? 'active' : '' }}" href="{{ route('products.index', ['tab' => 'products']) }}">
            <i class="bi bi-box-seam me-1"></i> Products
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'services' ? 'active' : '' }}" href="{{ route('products.index', ['tab' => 'services']) }}">
            <i class="bi bi-tools me-1"></i> Services
        </a>
    </li>
</ul>

{{-- Filter bar --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('products.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="form-label mb-0 fw-semibold text-nowrap">Filter:</label>
            @if($tab === 'services')
                <select name="category" class="form-select form-select-sm" style="max-width: 220px; min-width:140px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach(['Installation', 'Repair', 'Cabling', 'AMC', 'Other_Service'] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ str_replace('_', ' ', $cat) }}</option>
                    @endforeach
                </select>
            @else
                <select name="category" class="form-select form-select-sm" style="max-width: 220px; min-width:140px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach(['Camera', 'DVR_NVR', 'HDD', 'Cable', 'SMPS', 'Accessories', 'IP', 'Analog', 'Other'] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ str_replace('_', '/', $cat) }}</option>
                    @endforeach
                </select>
            @endif
            @if(request('category'))
                <a href="{{ route('products.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </form>
    </div>
</div>

{{-- Desktop Table View --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="productsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Name</th>
                        @if($tab === 'services')
                            <th class="text-end">Charge (₹)</th>
                        @else
                            <th class="text-end">Purchase Price</th>
                            <th class="text-end">Sale Price</th>
                            <th class="text-center">Stock</th>
                        @endif
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    @php
                        if ($tab !== 'services') {
                            if ($product->track_serial) {
                                $stockQty = $product->serialNumbers()->where('status', 'in_stock')->count();
                            } else {
                                $stockQty = $product->purchaseItems()->sum('qty') - $product->invoiceItems()->sum('qty');
                            }
                            $avgPurchasePrice = $product->purchaseItems()->avg('unit_price') ?? 0;
                        }
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none">
                                {{ $product->name }}
                            </a>
                            <div class="small text-muted">{{ str_replace('_', ' ', $product->category) }}{{ $product->brand ? ' · '.$product->brand : '' }}</div>
                        </td>
                        @if($tab === 'services')
                            <td class="text-end fw-semibold text-success">₹{{ number_format($product->sale_price ?? 0, 2) }}</td>
                        @else
                            <td class="text-end fw-semibold text-secondary">{{ $avgPurchasePrice > 0 ? '₹'.number_format($avgPurchasePrice, 2) : '—' }}</td>
                            <td class="text-end fw-semibold">₹{{ number_format($product->sale_price ?? 0, 2) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $stockQty > 0 ? 'bg-success' : 'bg-danger' }}">{{ $stockQty }}</span>
                            </td>
                        @endif
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Delete this {{ $tab === 'services' ? 'service' : 'product' }}?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- Mobile Card View --}}
<div class="d-md-none">
    @forelse($products as $product)
    @php
        if ($tab !== 'services') {
            if ($product->track_serial) {
                $stockQty = $product->serialNumbers()->where('status', 'in_stock')->count();
            } else {
                $stockQty = $product->purchaseItems()->sum('qty') - $product->invoiceItems()->sum('qty');
            }
        }
    @endphp
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1" style="min-width:0;">
                    <a href="{{ route('products.show', $product) }}" class="fw-bold text-decoration-none text-dark d-block text-truncate">
                        {{ $product->name }}
                    </a>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @if($tab === 'services')
                            <span class="badge bg-success">{{ str_replace('_', ' ', $product->category) }}</span>
                            <span class="badge bg-light text-dark border"><i class="bi bi-tools me-1"></i>Service</span>
                        @else
                            <span class="badge bg-secondary">{{ str_replace('_', '/', $product->category) }}</span>
                            @if($product->brand)
                                <span class="badge bg-light text-dark border">{{ $product->brand }}</span>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="text-end ms-2 flex-shrink-0">
                    <div class="fw-bold text-success">₹{{ number_format($product->sale_price ?? 0, 2) }}</div>
                    @if($tab !== 'services')
                        <span class="badge {{ $stockQty > 0 ? 'bg-success' : 'bg-danger' }} mt-1">
                            Stock: {{ $stockQty }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                <div class="small text-muted">
                    @if($product->model_number)
                        <i class="bi bi-cpu me-1"></i>{{ $product->model_number }}
                    @endif
                    @if($product->warranty_months)
                        <span class="ms-2"><i class="bi bi-shield-check me-1"></i>{{ $product->warranty_months }}m</span>
                    @endif
                </div>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info btn-sm" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST"
                          onsubmit="return confirm('Delete?')" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            @if($tab === 'services')
                <i class="bi bi-tools fs-1 d-block mb-2"></i>
                No services found. <a href="{{ route('products.create', ['type' => 'service']) }}">Add your first service</a>.
            @else
                <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                No products found. <a href="{{ route('products.create') }}">Add your first product</a>.
            @endif
        </div>
    </div>
    @endforelse
    <div class="d-flex justify-content-end mt-3">
        {{ $products->withQueryString()->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($(window).width() >= 768) {
            $('#productsTable').DataTable({
                paging: false,
                info: false,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [-1] }
                ],
                language: {
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No records found.</div>'
                }
            });
        }
    });
</script>
@endsection
