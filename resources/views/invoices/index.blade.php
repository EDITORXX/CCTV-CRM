@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Invoices</h4>
        <p class="text-muted mb-0">Manage sales invoices</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Invoice
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="invoicesTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Invoice Number</th>
                        <th>Customer</th>
                        <th>Site</th>
                        <th>Date</th>
                        <th>GST?</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ $invoice->customer->name ?? '—' }}</td>
                        <td>{{ $invoice->site->name ?? '—' }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                        <td>
                            @if($invoice->is_gst)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td class="fw-semibold">₹{{ number_format($invoice->grand_total, 2) }}</td>
                        <td>
                            @switch($invoice->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Draft</span>
                                    @break
                                @case('sent')
                                    <span class="badge bg-primary">Sent</span>
                                    @break
                                @case('paid')
                                    <span class="badge bg-success">Paid</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-dark" title="PDF" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                                      onsubmit="return confirm('Delete this invoice?')" class="d-inline">
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
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                            No invoices found. <a href="{{ route('invoices.create') }}">Create your first invoice</a>.
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
        $('#invoicesTable').DataTable({
            paging: true,
            pageLength: 25,
            order: [[4, 'desc']],
            columnDefs: [
                { orderable: false, targets: [8] }
            ]
        });
    });
</script>
@endsection
