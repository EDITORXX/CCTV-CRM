@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Products</h4>
        <p class="text-muted mb-0">Manage your product inventory</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.import') }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-excel me-1"></i> Import from Excel
        </a>
        <a href="{{ route('products.bulk-create') }}" class="btn btn-outline-secondary">
            <i class="bi bi-grid-3x3-gap me-1"></i> Add Multiple
        </a>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
    </div>
</div>

{{-- Category Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('products.index') }}" class="d-flex align-items-center gap-3">
            <label class="form-label mb-0 fw-semibold text-nowrap">Filter by Category:</label>
            <select name="category" class="form-select form-select-sm" style="max-width: 220px;" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach(['Camera', 'DVR/NVR', 'HDD', 'Cable', 'SMPS', 'Accessories', 'Other'] as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            @if(request('category'))
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg"></i> Clear
                </a>
            @endif
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="productsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Unit</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-center">Track Serial</th>
                        <th class="text-end">Stock Qty</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td><span class="badge bg-secondary">{{ $product->category }}</span></td>
                        <td>{{ $product->brand ?? '—' }}</td>
                        <td>{{ $product->model_number ?? '—' }}</td>
                        <td>{{ $product->unit }}</td>
                        <td class="text-end fw-semibold">{{ number_format($product->sale_price, 2) }}</td>
                        <td class="text-center">
                            @if($product->track_serial)
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Yes</span>
                            @else
                                <span class="badge bg-light text-muted">No</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="badge {{ ($product->stock_qty ?? 0) > 0 ? 'bg-primary' : 'bg-danger' }}">
                                {{ $product->stock_qty ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this product?')" class="d-inline">
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#productsTable').DataTable({
            paging: false,
            info: false,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [7, 9] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-box-seam fs-1 d-block mb-2"></i>No products found. <a href="{{ route('products.create') }}">Add your first product</a>.</div>'
            }
        });
    });
</script>
@endsection
