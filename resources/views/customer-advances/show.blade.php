@extends('layouts.app')

@section('title', 'Advance ' . $customerAdvance->receipt_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Advance Receipt {{ $customerAdvance->receipt_number }}</h4>
        <p class="text-muted mb-0">{{ $customerAdvance->customer->name ?? 'Unknown' }} — {{ $customerAdvance->payment_date->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customer-advances.receipt', $customerAdvance) }}" class="btn btn-outline-dark btn-sm" target="_blank">
            <i class="bi bi-printer me-1"></i> Print Receipt
        </a>
        <a href="{{ route('customer-advances.download', $customerAdvance) }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-download me-1"></i> Download PDF
        </a>
        <a href="{{ route('customer-advances.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-1"></i> Advance Info
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="140">Receipt No</td>
                        <td class="fw-semibold">{{ $customerAdvance->receipt_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Customer</td>
                        <td class="fw-semibold">{{ $customerAdvance->customer->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount</td>
                        <td class="fw-bold text-success fs-5">₹{{ number_format($customerAdvance->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Date</td>
                        <td>{{ $customerAdvance->payment_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Method</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $customerAdvance->payment_method)) }}</td>
                    </tr>
                    @if($customerAdvance->reference_number)
                    <tr>
                        <td class="text-muted">Reference</td>
                        <td>{{ $customerAdvance->reference_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Remaining balance</td>
                        <td class="fw-semibold">{{ $customerAdvance->remaining_balance > 0 ? '₹' . number_format($customerAdvance->remaining_balance, 2) : '— (fully allocated)' }}</td>
                    </tr>
                    @if($customerAdvance->notes)
                    <tr>
                        <td class="text-muted">Notes</td>
                        <td>{{ $customerAdvance->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt me-1"></i> Allocated to Invoices
            </div>
            <div class="card-body p-0">
                @if($customerAdvance->allocations->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th class="text-end">Amount Adjusted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customerAdvance->allocations as $alloc)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $alloc->invoice) }}">{{ $alloc->invoice->invoice_number ?? '-' }}</a>
                                </td>
                                <td>{{ $alloc->created_at->format('d M Y') }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($alloc->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-4 mb-0">Not yet adjusted to any invoice. Use “Use advance” on an invoice payment to adjust.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
