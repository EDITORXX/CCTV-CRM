@extends('layouts.app')

@section('title', 'Warranties')

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
        <h4 class="mb-1">Warranties</h4>
        <p class="text-muted mb-0">Track product warranties and expiry dates</p>
    </div>
    <a href="{{ route('warranties.search') }}" class="btn btn-outline-primary">
        <i class="bi bi-search me-1"></i> Advanced Search
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('warranties.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label for="search" class="form-label">Quick Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}" placeholder="Search by serial number, invoice number, or customer name...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('warranties.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="warrantiesTable">
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
                    @foreach($warranties as $warranty)
                    @php
                        $endDate = \Carbon\Carbon::parse($warranty->end_date);
                        $daysLeft = now()->diffInDays($endDate, false);
                        $expiringSoon = $warranty->status === 'active' && $daysLeft >= 0 && $daysLeft <= 30;
                    @endphp
                    <tr class="{{ $expiringSoon ? 'warranty-expiring-soon' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $warranty->product->name ?? '—' }}</td>
                        <td>{{ $warranty->customer->name ?? '—' }}</td>
                        <td><code>{{ $warranty->serialNumber->serial_number ?? '—' }}</code></td>
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
                            <form action="{{ route('warranties.update', $warranty) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;min-width:90px;">
                                    <option value="active" {{ $warranty->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ $warranty->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="replaced" {{ $warranty->status === 'replaced' ? 'selected' : '' }}>Replaced</option>
                                    <option value="rma" {{ $warranty->status === 'rma' ? 'selected' : '' }}>RMA</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($warranties->hasPages())
        <div class="p-3">
            {{ $warranties->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#warrantiesTable').DataTable({
            paging: false,
            info: false,
            searching: false,
            order: [[5, 'asc']],
            columnDefs: [
                { orderable: false, targets: [8] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-shield-check fs-1 d-block mb-2"></i>No warranties found.</div>'
            }
        });
    });
</script>
@endsection
