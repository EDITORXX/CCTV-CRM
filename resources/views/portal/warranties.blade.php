@extends('layouts.app')

@section('title', 'My Warranties')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">My Warranties</h4>
        <p class="text-muted mb-0">View warranty status for all your purchased products</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Serial Number</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warranties as $warranty)
                    <tr>
                        <td>
                            <strong>{{ $warranty->product->name ?? '-' }}</strong>
                            @if($warranty->product->brand ?? null)
                                <small class="text-muted d-block">{{ $warranty->product->brand }}</small>
                            @endif
                        </td>
                        <td>{{ $warranty->serialNumber->serial_number ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($warranty->end_date)->format('d M Y') }}</td>
                        <td>
                            @if($warranty->status === 'active' && \Carbon\Carbon::parse($warranty->end_date)->isFuture())
                                <span class="badge bg-success">Active</span>
                            @elseif($warranty->status === 'active' && \Carbon\Carbon::parse($warranty->end_date)->isPast())
                                <span class="badge bg-warning text-dark">Expired</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($warranty->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-shield-check fs-1 d-block mb-2"></i>
                            No warranties found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $warranties->links() }}
</div>
@endsection
