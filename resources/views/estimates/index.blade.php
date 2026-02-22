@extends('layouts.app')

@section('title', 'Estimates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Estimates</h4>
        <p class="text-muted mb-0">Manage quotations and estimates for customers</p>
    </div>
    <a href="{{ route('estimates.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Estimate
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="estimatesTable">
                <thead class="table-light">
                    <tr>
                        <th>Estimate #</th>
                        <th>Customer</th>
                        <th>Site</th>
                        <th>Date</th>
                        <th>Valid Until</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($estimates as $estimate)
                    <tr>
                        <td><strong>{{ $estimate->estimate_number }}</strong></td>
                        <td>{{ $estimate->customer->name ?? '-' }}</td>
                        <td>{{ $estimate->site->site_name ?? '-' }}</td>
                        <td>{{ $estimate->estimate_date->format('d M Y') }}</td>
                        <td>{{ $estimate->valid_until ? $estimate->valid_until->format('d M Y') : '-' }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($estimate->total, 2) }}</td>
                        <td>
                            <span class="badge
                                @if($estimate->status === 'draft') bg-secondary
                                @elseif($estimate->status === 'sent') bg-info
                                @elseif($estimate->status === 'accepted') bg-success
                                @elseif($estimate->status === 'rejected') bg-danger
                                @elseif($estimate->status === 'converted') bg-primary
                                @endif
                            ">{{ ucfirst($estimate->status) }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('estimates.show', $estimate) }}" class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!$estimate->isConverted())
                                <a href="{{ route('estimates.edit', $estimate) }}" class="btn btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                            No estimates yet. <a href="{{ route('estimates.create') }}">Create your first estimate</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $estimates->links() }}
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    @if($estimates->count() > 0)
    $('#estimatesTable').DataTable({
        paging: false,
        info: false,
        order: [[3, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });
    @endif
});
</script>
@endsection
