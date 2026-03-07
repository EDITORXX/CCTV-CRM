@extends('layouts.app')

@section('title', 'Payment Approvals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Payment Approvals</h4>
        <p class="text-muted mb-0">Review and approve customer payment submissions
            @if($pendingCount > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }} pending</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-1 flex-wrap">
        <a href="{{ route('customer-payments.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
        <a href="{{ route('customer-payments.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">Pending</a>
        <a href="{{ route('customer-payments.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success' : 'btn-outline-success' }}">Approved</a>
        <a href="{{ route('customer-payments.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">Rejected</a>
    </div>
</div>

{{-- Desktop table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Invoice</th>
                        <th>Amount</th>
                        <th>Screenshot</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $cp)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $cp->customer->name ?? '-' }}</strong>
                            <small class="text-muted d-block">{{ $cp->customer->phone ?? '' }}</small>
                        </td>
                        <td><code>{{ $cp->invoice->invoice_number ?? '-' }}</code></td>
                        <td class="fw-semibold">₹{{ number_format($cp->amount, 2) }}</td>
                        <td>
                            <a href="{{ asset('storage/' . $cp->screenshot) }}" target="_blank">
                                <img src="{{ asset('storage/' . $cp->screenshot) }}" alt="Screenshot"
                                     style="width:50px;height:50px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                            </a>
                        </td>
                        <td>
                            @if($cp->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($cp->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                                @if($cp->approver)
                                    <small class="text-muted d-block">by {{ $cp->approver->name }}</small>
                                @endif
                            @else
                                <span class="badge bg-danger">Rejected</span>
                                @if($cp->admin_notes)
                                    <small class="text-muted d-block">{{ Str::limit($cp->admin_notes, 30) }}</small>
                                @endif
                            @endif
                        </td>
                        <td class="text-muted small">{{ $cp->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($cp->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('customer-payments.approve', $cp) }}" method="POST"
                                      onsubmit="return confirm('Approve this payment of ₹{{ number_format($cp->amount, 2) }}?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg me-1"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $cp->id }}">
                                    <i class="bi bi-x-lg me-1"></i> Reject
                                </button>
                            </div>
                            @else
                                <span class="text-muted small">No action needed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-credit-card-2-front fs-1 d-block mb-2"></i>
                            No payment submissions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Mobile card view --}}
<div class="d-md-none">
    @forelse($payments as $cp)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="min-w-0">
                    <div class="fw-bold">{{ $cp->customer->name ?? '-' }}</div>
                    <small class="text-muted">{{ $cp->customer->phone ?? '' }}</small>
                </div>
                <span class="fw-bold text-dark ms-2 flex-shrink-0 fs-6">₹{{ number_format($cp->amount, 2) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ asset('storage/' . $cp->screenshot) }}" target="_blank">
                    <img src="{{ asset('storage/' . $cp->screenshot) }}" alt="Screenshot"
                         style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;">
                </a>
                <div>
                    <code class="small">{{ $cp->invoice->invoice_number ?? '-' }}</code>
                    <div class="d-flex gap-1 mt-1">
                        @if($cp->status === 'pending')
                            <span class="badge bg-warning text-dark" style="font-size:.7rem;">Pending</span>
                        @elseif($cp->status === 'approved')
                            <span class="badge bg-success" style="font-size:.7rem;">Approved</span>
                        @else
                            <span class="badge bg-danger" style="font-size:.7rem;">Rejected</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $cp->created_at->format('d M Y, h:i A') }}</small>
                @if($cp->status === 'pending')
                <div class="d-flex gap-1">
                    <form action="{{ route('customer-payments.approve', $cp) }}" method="POST"
                          onsubmit="return confirm('Approve ₹{{ number_format($cp->amount, 2) }}?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success py-0 px-2"><i class="bi bi-check-lg"></i></button>
                    </form>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2"
                            data-bs-toggle="modal" data-bs-target="#rejectModal{{ $cp->id }}">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-credit-card-2-front fs-1 d-block mb-2"></i>
        <p>No payment submissions found.</p>
    </div>
    @endforelse
</div>

{{-- Reject Modals (shared for both views) --}}
@foreach($payments as $cp)
    @if($cp->status === 'pending')
    <div class="modal fade" id="rejectModal{{ $cp->id }}" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <form action="{{ route('customer-payments.reject', $cp) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title">Reject Payment</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Reason (optional)</label>
                        <textarea class="form-control" name="admin_notes" rows="3" placeholder="Why is this being rejected?"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

<div class="mt-3">
    {{ $payments->appends(request()->query())->links() }}
</div>
@endsection
