@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
    .dash-blur-wrapper {
        position: relative;
    }
    .dash-blur-content {
        transition: filter .4s ease;
    }
    .dash-blur-content.blurred {
        filter: blur(8px);
        pointer-events: none;
        user-select: none;
    }
    .dash-blur-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        background: rgba(255,255,255,.15);
    }
    .dash-blur-overlay .unblur-btn {
        padding: .75rem 2rem;
        font-size: 1rem;
        border-radius: 2rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.15);
    }

    @media (min-width: 768px) {
        .dash-blur-wrapper .dash-blur-overlay,
        .dash-blur-content.blurred { display: none !important; filter: none !important; pointer-events: auto; user-select: auto; }
        .dash-blur-content { filter: none !important; }
    }
</style>
@endsection

@section('content')

<div class="dash-blur-wrapper">
    {{-- Blur overlay (mobile only) --}}
    <div class="dash-blur-overlay d-md-none" id="dashBlurOverlay">
        <button type="button" class="btn btn-primary unblur-btn" id="unblurBtn">
            <i class="bi bi-eye me-2"></i> Tap to View Dashboard
        </button>
    </div>

    <div class="dash-blur-content blurred" id="dashContent">

{{-- Row 1: Main stat cards (2 per row on mobile) --}}
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Monthly Sales</div>
                    <h5 class="mb-0 fw-bold text-success fs-6 fs-md-5">₹{{ number_format($monthlySales ?? 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-cart-plus"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Monthly Purchases</div>
                    <h5 class="mb-0 fw-bold text-info fs-6 fs-md-5">₹{{ number_format($monthlyPurchases ?? 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center {{ ($monthlyProfit ?? 0) >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Net Profit</div>
                    <h5 class="mb-0 fw-bold {{ ($monthlyProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fs-6 fs-md-5">
                        ₹{{ number_format($monthlyProfit ?? 0) }}
                    </h5>
                    <small class="text-muted d-none d-md-block" style="line-height:1.4;">
                        Sale ₹{{ number_format($monthlySalesWithoutGst ?? 0) }} - Cost ₹{{ number_format($monthlyCOGS ?? 0) }}<br>
                        - Reg ₹{{ number_format($monthlyRegularExpenses ?? 0) }} - Site ₹{{ number_format($monthlySiteExpenses ?? 0) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Stock Value</div>
                    <h5 class="mb-0 fw-bold text-primary fs-6 fs-md-5">₹{{ number_format($totalStockValue ?? 0) }}</h5>
                    <small class="text-muted d-none d-sm-block">{{ count($stockDetails ?? []) }} products</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row 2: Secondary stat cards (2 per row on mobile) --}}
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-headset"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Today's Tickets</div>
                    <h5 class="mb-0 fw-bold fs-6 fs-md-5">{{ $todayTickets ?? 0 }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Low Stock Items</div>
                    <h5 class="mb-0 fw-bold text-danger fs-6 fs-md-5">{{ $lowStockCount ?? 0 }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Warranties Expiring</div>
                    <h5 class="mb-0 fw-bold text-warning fs-6 fs-md-5">{{ count($expiringWarranties ?? []) }}</h5>
                    <small class="text-muted d-none d-sm-block">within 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-2 gap-md-3 p-2 p-md-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary flex-shrink-0" style="width:42px;height:42px;font-size:1.2rem;">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-muted small fw-medium text-truncate">Monthly COGS</div>
                    <h5 class="mb-0 fw-bold fs-6 fs-md-5">₹{{ number_format($monthlyCOGS ?? 0) }}</h5>
                    <small class="text-muted d-none d-sm-block">cost of goods sold</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- This month: Invoices by user, Regular Exp, Site Exp --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt me-2 text-primary"></i>This month</h6>
            </div>
            <div class="card-body py-2">
                <div class="row g-3 align-items-start">
                    <div class="col-4">
                        <div class="text-muted small">Regular Exp.</div>
                        <div class="fw-bold text-primary">₹{{ number_format($monthlyRegularExpenses ?? 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Site Exp.</div>
                        <div class="fw-bold text-info">₹{{ number_format($monthlySiteExpenses ?? 0) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Sales</div>
                        <div class="fw-bold text-success">₹{{ number_format($monthlySales ?? 0) }}</div>
                    </div>
                </div>
                @if(count($invoicesByUser ?? []) > 0)
                <div class="mt-3 pt-3 border-top">
                    <div class="text-muted small fw-medium mb-2">Invoices by user</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoicesByUser as $row)
                                <tr>
                                    <td>{{ $row['user_name'] }}</td>
                                    <td class="text-center">{{ $row['count'] }}</td>
                                    <td class="text-end">₹{{ number_format($row['total'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Current Stock Summary --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-box-seam me-2 text-primary"></i>Stock Summary</h6>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">All Products</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end d-none d-sm-table-cell">Avg Cost</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sorted = collect($stockDetails ?? [])->sortByDesc('value')->take(15); @endphp
                            @forelse($sorted as $item)
                            <tr>
                                <td>
                                    {{ $item['product']->name }}
                                    <span class="badge bg-light text-dark ms-1 d-none d-md-inline">{{ str_replace('_', '/', $item['product']->category) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $item['qty'] <= 5 ? 'bg-danger' : 'bg-success' }}">{{ $item['qty'] }} {{ $item['product']->unit }}</span>
                                </td>
                                <td class="text-end d-none d-sm-table-cell">₹{{ number_format($item['avg_cost'], 2) }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($item['value']) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>No stock data
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($stockDetails ?? []) > 0)
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td>Total</td>
                                <td class="text-center">{{ collect($stockDetails)->sum('qty') }}</td>
                                <td class="d-none d-sm-table-cell"></td>
                                <td class="text-end text-primary">₹{{ number_format($totalStockValue) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Tickets --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-headset me-2 text-warning"></i>Recent Tickets</h6>
                <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket #</th>
                                <th>Customer</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="d-none d-sm-table-cell">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTickets ?? [] as $ticket)
                            <tr>
                                <td><a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold text-decoration-none">#{{ $ticket->ticket_number }}</a></td>
                                <td>{{ $ticket->customer->name ?? '—' }}</td>
                                <td>
                                    <span class="badge
                                        @if($ticket->priority === 'high') bg-danger
                                        @elseif($ticket->priority === 'medium') bg-warning text-dark
                                        @else bg-secondary
                                        @endif
                                    ">{{ ucfirst($ticket->priority) }}</span>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($ticket->status === 'open') bg-primary
                                        @elseif($ticket->status === 'in_progress') bg-warning text-dark
                                        @elseif($ticket->status === 'resolved') bg-success
                                        @else bg-secondary
                                        @endif
                                    ">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                </td>
                                <td class="text-muted small d-none d-sm-table-cell">{{ $ticket->created_at->format('d M') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>No recent tickets
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row: Low Stock Alerts & Expiring Warranties --}}
<div class="row g-4 mt-1">
    @if(count($lowStockProducts ?? []) > 0)
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 border-start border-danger border-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alert</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Available</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts as $item)
                            <tr>
                                <td>{{ $item['product']->name }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ $item['qty'] }} {{ $item['product']->unit }}</span></td>
                                <td class="text-end">₹{{ number_format($item['value']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="{{ count($lowStockProducts ?? []) > 0 ? 'col-lg-6' : 'col-lg-12' }}">
        <div class="card shadow-sm border-0 border-start border-warning border-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold text-warning"><i class="bi bi-shield-exclamation me-2"></i>Warranties Expiring</h6>
                <a href="{{ route('warranties.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th class="d-none d-sm-table-cell">Product</th>
                                <th>Expires</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringWarranties ?? [] as $warranty)
                            <tr>
                                <td>{{ $warranty->customer->name ?? '—' }}</td>
                                <td class="d-none d-sm-table-cell">{{ $warranty->product->name ?? '—' }}</td>
                                <td>{{ $warranty->end_date->format('d M Y') }}</td>
                                <td>
                                    @php $daysLeft = now()->diffInDays($warranty->end_date, false); @endphp
                                    <span class="badge {{ $daysLeft <= 7 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ round($daysLeft) }} days</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-shield-check fs-4 d-block mb-1"></i>No warranties expiring soon
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>{{-- /dash-blur-content --}}
</div>{{-- /dash-blur-wrapper --}}

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#unblurBtn').on('click', function() {
        $('#dashContent').removeClass('blurred');
        $('#dashBlurOverlay').fadeOut(300);
    });
});
</script>
@endsection
