@extends('layouts.app')

@section('title', 'Warranty Search')

@section('styles')
<style>
    .warranty-expiring-soon {
        background-color: #fff3cd !important;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Warranty Search</h4>
        <p class="text-muted mb-0">Search warranties by serial number, invoice, or customer</p>
    </div>
    <a href="{{ route('warranties.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Warranties
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('warranties.search') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-10">
                <label for="q" class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control form-control-lg" id="q" name="q"
                           value="{{ request('q') }}" placeholder="Enter serial number, invoice number, or customer name..." autofocus>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">Search</button>
            </div>
        </form>
    </div>
</div>

@if(request('q'))
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-list-ul me-1"></i> Results for "{{ request('q') }}"
        @if(isset($warranties))
            <span class="badge bg-primary ms-2">{{ $warranties->count() }} found</span>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="searchResultsTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Serial Number</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Days Left</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warranties ?? [] as $warranty)
                    @php
                        $endDate = \Carbon\Carbon::parse($warranty->end_date);
                        $daysLeft = now()->diffInDays($endDate, false);
                        $expiringSoon = $warranty->status === 'active' && $daysLeft >= 0 && $daysLeft <= 30;
                    @endphp
                    <tr class="{{ $expiringSoon ? 'warranty-expiring-soon' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $warranty->product->name ?? '—' }}</td>
                        <td>{{ $warranty->customer->name ?? '—' }}</td>
                        <td><code>{{ $warranty->serial_number ?? '—' }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }}</td>
                        <td>{{ $endDate->format('d M Y') }}</td>
                        <td>
                            @switch($warranty->status)
                                @case('active')
                                    <span class="badge bg-success">Active</span>
                                    @break
                                @case('expired')
                                    <span class="badge bg-danger">Expired</span>
                                    @break
                                @case('replaced')
                                    <span class="badge bg-warning text-dark">Replaced</span>
                                    @break
                                @case('rma')
                                    <span class="badge bg-info">RMA</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($warranty->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if($warranty->status === 'active')
                                @if($daysLeft > 0)
                                    <span class="{{ $daysLeft <= 30 ? 'text-warning fw-bold' : 'text-success' }}">
                                        {{ $daysLeft }} days
                                    </span>
                                @else
                                    <span class="text-danger fw-bold">Expired</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('warranties.show', $warranty) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-search fs-1 d-block mb-2"></i>
                            No warranties match your search criteria.
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
        $('#searchResultsTable').DataTable({
            paging: false,
            info: false,
            order: [[5, 'asc']],
            columnDefs: [
                { orderable: false, targets: [8] }
            ]
        });
    });
</script>
@endsection
