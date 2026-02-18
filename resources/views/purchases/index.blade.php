@extends('layouts.app')

@section('title', 'Purchases')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Purchases</h4>
        <p class="text-muted mb-0">Manage purchase bills from vendors</p>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Purchase
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="purchasesTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Vendor</th>
                        <th>Bill Number</th>
                        <th>Bill Date</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>GST</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $purchase->vendor->name ?? '—' }}</td>
                        <td><code>{{ $purchase->bill_number }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($purchase->bill_date)->format('d M Y') }}</td>
                        <td><span class="badge bg-info">{{ $purchase->items_count ?? $purchase->items->count() }}</span></td>
                        <td class="fw-semibold">₹{{ number_format($purchase->total_amount, 2) }}</td>
                        <td>₹{{ number_format($purchase->gst_amount, 2) }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('purchases.destroy', $purchase) }}" method="POST"
                                      onsubmit="return confirm('Delete this purchase?')" class="d-inline">
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
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-cart-plus fs-1 d-block mb-2"></i>
                            No purchases found. <a href="{{ route('purchases.create') }}">Record your first purchase</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#purchasesTable').DataTable({
            paging: true,
            pageLength: 25,
            order: [[3, 'desc']],
            columnDefs: [
                { orderable: false, targets: [7] }
            ]
        });
    });
</script>
@endsection
