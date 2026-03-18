@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $invoice->is_gst ? 'Tax Invoice' : 'Bill of Supply' }} — {{ $invoice->invoice_number }}</h4>
        <p class="text-muted mb-0">{{ $invoice->customer->name ?? 'Unknown' }} — {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($invoice->status === 'draft')
            <form action="{{ route('invoices.update', $invoice) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="sent">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-send me-1"></i> Mark as Sent
                </button>
            </form>
        @endif
        {{-- Share Link Button --}}
        <button type="button" class="btn btn-outline-success btn-sm" id="shareBtn"
            data-invoice-id="{{ $invoice->id }}"
            data-token="{{ $invoice->share_token }}"
            data-token-url="{{ route('invoices.share-token', $invoice) }}"
            data-csrf="{{ csrf_token() }}">
            <i class="bi bi-share me-1"></i> Share Link
        </button>
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-dark btn-sm" target="_blank">
            <i class="bi bi-printer me-1"></i> Print PDF
        </a>
        <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-download me-1"></i> Download PDF
        </a>
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Delete this invoice?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </form>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Invoice Header Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-1"></i> Invoice Info
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="110">Invoice #</td>
                        <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                    </tr>
                    @if($invoice->remaining_due_date)
                    <tr>
                        <td class="text-muted">Remaining Due</td>
                        <td>{{ $invoice->remaining_due_date->format('d M Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Status</td>
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
                    </tr>
                    <tr>
                        <td class="text-muted">Customer</td>
                        <td class="fw-semibold">{{ $invoice->customer->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Site</td>
                        <td>{{ $invoice->site->site_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">GST Invoice</td>
                        <td>
                            @if($invoice->is_gst)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                    </tr>
                    @if($invoice->notes)
                    <tr>
                        <td class="text-muted">Notes</td>
                        <td>{{ $invoice->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-list-ul me-1"></i> Line Items
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th width="50">Qty</th>
                                <th width="100">Rate</th>
                                @if($invoice->is_gst)
                                <th width="60">GST%</th>
                                @endif
                                <th width="90">Discount</th>
                                <th width="110">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $subtotal = 0; $totalGst = 0; $totalItemDiscount = 0; @endphp
                            @forelse($invoice->items as $item)
                            @php
                                $lineBase = $item->qty * $item->unit_price;
                                $lineGst = $invoice->is_gst ? ($lineBase * ($item->gst_percent / 100)) : 0;
                                $lineDiscount = $item->discount ?? 0;
                                $lineTotal = $lineBase + $lineGst - $lineDiscount;
                                $subtotal += $lineBase;
                                $totalGst += $lineGst;
                                $totalItemDiscount += $lineDiscount;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name ?? '—' }}</div>
                                    @if($item->serialNumbers && $item->serialNumbers->count())
                                        <div class="mt-1">
                                            @foreach($item->serialNumbers as $sn)
                                                <span class="badge bg-secondary me-1 mb-1">{{ $sn->serial_number }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $item->qty }}</td>
                                <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                @if($invoice->is_gst)
                                <td>{{ $item->gst_percent }}%</td>
                                @endif
                                <td>₹{{ number_format($lineDiscount, 2) }}</td>
                                <td class="fw-semibold">₹{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $invoice->is_gst ? 6 : 5 }}" class="text-center text-muted py-3">No items</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="{{ $invoice->is_gst ? 5 : 4 }}" class="text-end">Subtotal:</td>
                                <td class="fw-semibold">₹{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->is_gst)
                            <tr>
                                <td colspan="5" class="text-end">GST:</td>
                                <td class="fw-semibold">₹{{ number_format($totalGst, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->discount > 0)
                            <tr>
                                <td colspan="{{ $invoice->is_gst ? 5 : 4 }}" class="text-end">Discount:</td>
                                <td class="fw-semibold text-danger">-₹{{ number_format($invoice->discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="{{ $invoice->is_gst ? 5 : 4 }}" class="text-end fw-bold">Grand Total:</td>
                                <td class="fw-bold text-success fs-5">₹{{ number_format($invoice->total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Internal expenses & profit --}}
@php
    $totalExpenses = $invoice->invoiceExpenses->sum('amount');
    $profit = $invoice->total - $totalExpenses;
@endphp
<div class="row g-4 mt-2">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-cash-stack me-1"></i> Internal expenses & profit
            </div>
            <div class="card-body">
                @if($invoice->invoiceExpenses && $invoice->invoiceExpenses->count() > 0)
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th width="120" class="text-end">Amount</th>
                                <th width="80"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->invoiceExpenses as $exp)
                            <tr>
                                <td>{{ $exp->description }}</td>
                                <td class="text-end">₹{{ number_format($exp->amount, 2) }}</td>
                                <td>
                                    <form action="{{ route('invoice-expenses.destroy', $exp) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this expense?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted mb-3">No internal expenses. Add below to track costs and see profit.</p>
                @endif
                <div class="mb-3">
                    <table class="table table-sm table-borderless mb-0 w-auto">
                        <tr>
                            <td class="text-muted">Total expenses</td>
                            <td class="fw-semibold ps-3">₹{{ number_format($totalExpenses, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Profit</td>
                            <td class="fw-bold text-success fs-5 ps-3">₹{{ number_format($profit, 2) }}</td>
                        </tr>
                    </table>
                </div>
                <form action="{{ route('invoices.expenses.store', $invoice) }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label for="exp_description" class="form-label small">Description</label>
                        <input type="text" class="form-control form-control-sm" id="exp_description" name="description" placeholder="e.g. Labour, material" required maxlength="255">
                    </div>
                    <div class="col-md-3">
                        <label for="exp_amount" class="form-label small">Amount (₹)</label>
                        <input type="number" class="form-control form-control-sm" id="exp_amount" name="amount" min="0" step="0.01" value="0" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-plus-lg me-1"></i> Add expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Payment Section --}}
<div class="row g-4 mt-2">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-cash-stack me-1"></i> Payment Summary
            </div>
            <div class="card-body">
                @php
                    $totalPaid = $invoice->payments->sum('amount') ?? 0;
                    $balanceDue = $invoice->total - $totalPaid;
                    $advanceBalance = $invoice->customer->getAdvanceBalance();
                    $maxAdvanceUse = $balanceDue > 0 && $advanceBalance > 0 ? min($balanceDue, $advanceBalance) : 0;
                @endphp
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="text-muted small">Total Amount</div>
                        <div class="fw-bold fs-5">₹{{ number_format($invoice->total, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Total Paid</div>
                        <div class="fw-bold fs-5 text-success">₹{{ number_format($totalPaid, 2) }}</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Balance Due</div>
                        <div class="fw-bold fs-5 {{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($balanceDue, 2) }}</div>
                    </div>
                </div>

                @if($invoice->payments && $invoice->payments->count())
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                            @php
                                $methodLabels = [
                                    'cash' => 'Cash',
                                    'upi' => 'UPI',
                                    'bank_transfer' => 'Bank Transfer',
                                    'cheque' => 'Cheque',
                                    'card' => 'Card',
                                ];
                                $methodLabel = $methodLabels[$payment->payment_method ?? ''] ?? ucfirst(str_replace('_', ' ', $payment->payment_method ?? ''));
                            @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                <td class="fw-semibold">₹{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $methodLabel }}</td>
                                <td>{{ $payment->reference_number ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center mb-0">No payments recorded yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Add Payment
            </div>
            <div class="card-body">
                @if($advanceBalance > 0)
                    <p class="text-success small mb-2">
                        <i class="bi bi-wallet2 me-1"></i> Customer advance balance: <strong>₹{{ number_format($advanceBalance, 2) }}</strong>
                        — use below to adjust against this invoice.
                    </p>
                @endif
                <form action="{{ route('payments.store', $invoice) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="pay_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror" id="pay_amount" name="amount"
                                   min="0.01" step="0.01" max="{{ $balanceDue }}" value="{{ old('amount') }}" required>
                            @error('amount')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="pay_use_advance" class="form-label">Use advance (₹)</label>
                            <input type="number" class="form-control @error('use_advance') is-invalid @enderror" id="pay_use_advance" name="use_advance"
                                   min="0" step="0.01" max="{{ $maxAdvanceUse }}" value="{{ old('use_advance', 0) }}" placeholder="0">
                            @if($maxAdvanceUse > 0)<small class="text-muted">Max ₹{{ number_format($maxAdvanceUse, 2) }}</small>@endif
                            @error('use_advance')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="pay_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="pay_date" name="payment_date"
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="pay_method" class="form-label">Payment method</label>
                            <select class="form-select" id="pay_method" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="card">Card</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="pay_reference" class="form-label">Reference</label>
                            <input type="text" class="form-control" id="pay_reference" name="reference_number"
                                   placeholder="Transaction ID, Cheque #, etc." value="{{ old('reference_number') }}">
                        </div>
                        <div class="col-12">
                            <label for="pay_notes" class="form-label">Notes</label>
                            <input type="text" class="form-control" id="pay_notes" name="notes"
                                   placeholder="Optional notes" value="{{ old('notes') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i> Record Payment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- Share Link Modal --}}
<div class="modal fade" id="shareLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-share me-1"></i> Share Bill of Supply</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Customer ko ye link bhejein. Woh bina login ke bill dekh sakte hain aur digitally sign bhi kar sakte hain.</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-sm" id="shareLinkInput" readonly>
                    <button class="btn btn-outline-secondary btn-sm" id="copyLinkBtn" type="button">
                        <i class="bi bi-clipboard me-1"></i> Copy
                    </button>
                </div>
                <div id="copyMsg" class="d-none text-success small"><i class="bi bi-check-circle me-1"></i> Link copied!</div>
                @if($invoice->customer_signed_at)
                <div class="alert alert-success py-2 small mt-2 mb-0">
                    <i class="bi bi-pen me-1"></i>
                    Customer ne <strong>{{ $invoice->customer_signed_at->format('d M Y, h:i A') }}</strong> ko digitally sign kiya hai.
                    <br><span class="text-muted">IP: {{ $invoice->customer_ip }}</span>
                </div>
                @else
                <div class="alert alert-warning py-2 small mt-2 mb-0">
                    <i class="bi bi-hourglass me-1"></i> Customer ne abhi sign nahi kiya hai.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
document.getElementById('shareBtn').addEventListener('click', function() {
    var btn = this;
    var existingToken = btn.dataset.token;
    var tokenUrl = btn.dataset.tokenUrl;
    var csrf = btn.dataset.csrf;

    function showModal(url) {
        document.getElementById('shareLinkInput').value = url;
        new bootstrap.Modal(document.getElementById('shareLinkModal')).show();
    }

    if (existingToken) {
        showModal(window.location.origin + '/bill/' + existingToken);
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    fetch(tokenUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        btn.dataset.token = data.token;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-share me-1"></i> Share Link';
        showModal(data.url);
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-share me-1"></i> Share Link';
    });
});

document.getElementById('copyLinkBtn').addEventListener('click', function() {
    var input = document.getElementById('shareLinkInput');
    navigator.clipboard.writeText(input.value).then(function() {
        var msg = document.getElementById('copyMsg');
        msg.classList.remove('d-none');
        setTimeout(function() { msg.classList.add('d-none'); }, 2000);
    });
});
</script>
@endsection

@endsection
