@extends('layouts.app')

@section('title', 'Serial Number Search')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Serial Number Search</h4>
        <p class="text-muted mb-0">Track serial numbers across purchases, sales, and installations</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('serials.search') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-9">
                <label for="q" class="form-label">Search Serial Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" class="form-control form-control-lg" id="q" name="q"
                           value="{{ request('q') }}" placeholder="Enter full or partial serial number..." autofocus>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

@if(request('q'))
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-list-ul me-1"></i> Results for "{{ request('q') }}"
        @if(isset($serials))
            <span class="badge bg-primary ms-2">{{ $serials->count() }} found</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="serialsTable">
                <thead class="table-light">
                    <tr>
                        <th>Serial Number</th>
                        <th>Product</th>
                        <th>Status</th>
                        <th>Purchased From</th>
                        <th>Purchase Date</th>
                        <th>Sold To</th>
                        <th>Invoice #</th>
                        <th>Installed Site</th>
                        <th>Warranty</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serials ?? [] as $serial)
                    <tr>
                        <td><code class="fw-bold">{{ $serial->serial_number }}</code></td>
                        <td>{{ $serial->product->name ?? '—' }}</td>
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
                        <td>{{ $serial->purchaseItem->purchase->vendor->name ?? '—' }}</td>
                        <td>{{ $serial->purchaseItem ? \Carbon\Carbon::parse($serial->purchaseItem->purchase->bill_date)->format('d M Y') : '—' }}</td>
                        <td>{{ $serial->invoiceItem->invoice->customer->name ?? '—' }}</td>
                        <td>
                            @if($serial->invoiceItem && $serial->invoiceItem->invoice)
                                <a href="{{ route('invoices.show', $serial->invoiceItem->invoice) }}" class="text-decoration-none">
                                    {{ $serial->invoiceItem->invoice->invoice_number }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $serial->site->name ?? '—' }}</td>
                        <td>
                            @if($serial->warranty)
                                @switch($serial->warranty->status)
                                    @case('active')
                                        <span class="badge bg-success">Active</span>
                                        @break
                                    @case('expired')
                                        <span class="badge bg-danger">Expired</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst($serial->warranty->status) }}</span>
                                @endswitch
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('serials.show', $serial) }}" class="btn btn-sm btn-outline-info" title="Full Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="bi bi-upc-scan fs-1 d-block mb-2"></i>
                            No serial numbers match your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#serialsTable').DataTable({
            paging: false,
            info: false,
            searching: false,
            order: [[0, 'asc']]
        });
    });
</script>
@endsection
