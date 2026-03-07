@extends('layouts.app')

@section('title', 'Warranties')

@section('styles')
<style>
    .warranty-expiring-soon {
        background-color: #fff3cd !important;
    }
    .warranty-card-expiring {
        border-left: 4px solid #ffc107 !important;
        background-color: #fffdf5;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Warranties</h4>
        <p class="text-muted mb-0">Track product warranties and expiry dates</p>
    </div>
    <a href="{{ route('warranties.search') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-search me-1"></i> Advanced Search
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form action="{{ route('warranties.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col">
                <input type="text" class="form-control form-control-sm" name="search"
                       value="{{ request('search') }}" placeholder="Search serial, invoice, customer...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if(request('search'))
            <div class="col-auto">
                <a href="{{ route('warranties.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Desktop Table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
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

{{-- Mobile Card View --}}
<div class="d-md-none">
    @forelse($warranties as $warranty)
    @php
        $endDate = \Carbon\Carbon::parse($warranty->end_date);
        $daysLeft = now()->diffInDays($endDate, false);
        $expiringSoon = $warranty->status === 'active' && $daysLeft >= 0 && $daysLeft <= 30;
    @endphp
    <div class="card border-0 shadow-sm mb-2 {{ $expiringSoon ? 'warranty-card-expiring' : '' }}">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1" style="min-width:0;">
                    <div class="fw-bold text-truncate">{{ $warranty->product->name ?? '—' }}</div>
                    <div class="small text-muted">{{ $warranty->customer->name ?? '—' }}</div>
                </div>
                <div class="text-end ms-2 flex-shrink-0">
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
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge bg-light text-dark border"><i class="bi bi-upc-scan me-1"></i>{{ $warranty->serialNumber->serial_number ?? '—' }}</span>
                @if($warranty->status === 'active')
                    @if($daysLeft > 0)
                        <span class="badge {{ $daysLeft <= 30 ? 'bg-warning text-dark' : 'bg-success' }}">
                            <i class="bi bi-clock me-1"></i>{{ $daysLeft }} days left
                        </span>
                    @else
                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Expired</span>
                    @endif
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                <div class="small text-muted">
                    <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                </div>
                <form action="{{ route('warranties.update', $warranty) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto;min-width:80px;font-size:0.75rem;">
                        <option value="active" {{ $warranty->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ $warranty->status === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="replaced" {{ $warranty->status === 'replaced' ? 'selected' : '' }}>Replaced</option>
                        <option value="rma" {{ $warranty->status === 'rma' ? 'selected' : '' }}>RMA</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-shield-check fs-1 d-block mb-2"></i>
            No warranties found.
        </div>
    </div>
    @endforelse
    @if($warranties->hasPages())
    <div class="d-flex justify-content-end mt-3">
        {{ $warranties->links() }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($(window).width() >= 768) {
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
        }
    });
</script>
@endsection
