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

{{-- Desktop table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
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
                    @foreach($invoices as $invoice)
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Mobile card view --}}
<div class="d-md-none">
    @forelse($invoices as $invoice)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="min-w-0">
                    <a href="{{ route('invoices.show', $invoice) }}" class="fw-bold text-decoration-none">{{ $invoice->invoice_number }}</a>
                    <div class="text-truncate">{{ $invoice->customer->name ?? '—' }}</div>
                </div>
                <span class="fw-bold text-dark ms-2 flex-shrink-0">₹{{ number_format($invoice->grand_total, 2) }}</span>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-2">
                @switch($invoice->status)
                    @case('draft')
                        <span class="badge bg-secondary" style="font-size:.7rem;">Draft</span>
                        @break
                    @case('sent')
                        <span class="badge bg-primary" style="font-size:.7rem;">Sent</span>
                        @break
                    @case('paid')
                        <span class="badge bg-success" style="font-size:.7rem;">Paid</span>
                        @break
                    @case('cancelled')
                        <span class="badge bg-danger" style="font-size:.7rem;">Cancelled</span>
                        @break
                    @default
                        <span class="badge bg-secondary" style="font-size:.7rem;">{{ ucfirst($invoice->status) }}</span>
                @endswitch
                @if($invoice->is_gst)
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.7rem;">GST</span>
                @endif
                @if($invoice->site)
                    <span class="badge bg-light text-dark border" style="font-size:.7rem;">{{ $invoice->site->name }}</span>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</small>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-dark btn-sm" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                          onsubmit="return confirm('Delete this invoice?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
        <p>No invoices found. <a href="{{ route('invoices.create') }}">Create your first invoice</a>.</p>
    </div>
    @endforelse
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
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-receipt fs-1 d-block mb-2"></i>No invoices found. <a href="{{ route('invoices.create') }}">Create your first invoice</a>.</div>'
            }
        });
    });
</script>
@endsection
