@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- Row 1: Main stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Monthly Sales</div>
                    <h4 class="mb-0 fw-bold text-success">₹{{ number_format($monthlySales ?? 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-cart-plus"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Monthly Purchases</div>
                    <h4 class="mb-0 fw-bold text-info">₹{{ number_format($monthlyPurchases ?? 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center {{ ($monthlyProfit ?? 0) >= 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Net Profit (This Month)</div>
                    <h4 class="mb-0 fw-bold {{ ($monthlyProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                        ₹{{ number_format($monthlyProfit ?? 0) }}
                    </h4>
                    <small class="text-muted">Sale ₹{{ number_format($monthlySalesWithoutGst ?? 0) }} - Cost ₹{{ number_format($monthlyCOGS ?? 0) }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Total Stock Value</div>
                    <h4 class="mb-0 fw-bold text-primary">₹{{ number_format($totalStockValue ?? 0) }}</h4>
                    <small class="text-muted">{{ count($stockDetails ?? []) }} products in stock</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Row 2: Secondary stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-headset"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Today's Tickets</div>
                    <h4 class="mb-0 fw-bold">{{ $todayTickets ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Low Stock Items</div>
                    <h4 class="mb-0 fw-bold text-danger">{{ $lowStockCount ?? 0 }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Warranties Expiring</div>
                    <h4 class="mb-0 fw-bold text-warning">{{ count($expiringWarranties ?? []) }}</h4>
                    <small class="text-muted">within 30 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary" style="width:50px;height:50px;font-size:1.4rem;">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Monthly COGS</div>
                    <h4 class="mb-0 fw-bold">₹{{ number_format($monthlyCOGS ?? 0) }}</h4>
                    <small class="text-muted">cost of goods sold</small>
                </div>
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
                                <th class="text-end">Avg Cost</th>
                                <th class="text-end">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sorted = collect($stockDetails ?? [])->sortByDesc('value')->take(15); @endphp
                            @forelse($sorted as $item)
                            <tr>
                                <td>
                                    {{ $item['product']->name }}
                                    <span class="badge bg-light text-dark ms-1">{{ str_replace('_', '/', $item['product']->category) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $item['qty'] <= 5 ? 'bg-danger' : 'bg-success' }}">{{ $item['qty'] }} {{ $item['product']->unit }}</span>
                                </td>
                                <td class="text-end">₹{{ number_format($item['avg_cost'], 2) }}</td>
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
                                <td class="text-center">{{ collect($stockDetails)->sum('qty') }} items</td>
                                <td></td>
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
                <h6 class="mb-0 fw-semibold"><i class="bi bi-headset me-2 text-warning"></i>Recent Service Tickets</h6>
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
                                <th>Date</th>
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
                                <td class="text-muted small">{{ $ticket->created_at->format('d M') }}</td>
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
                <h6 class="mb-0 fw-semibold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alert (5 or less)</h6>
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
                <h6 class="mb-0 fw-semibold text-warning"><i class="bi bi-shield-exclamation me-2"></i>Warranties Expiring (30 days)</h6>
                <a href="{{ route('warranties.index') }}" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Expires</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expiringWarranties ?? [] as $warranty)
                            <tr>
                                <td>{{ $warranty->customer->name ?? '—' }}</td>
                                <td>{{ $warranty->product->name ?? '—' }}</td>
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

@endsection
