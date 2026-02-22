@extends('layouts.app')

@section('title', 'My Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">My Payments</h4>
        <p class="text-muted mb-0">Submit and track your payment confirmations</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitPaymentModal">
        <i class="bi bi-upload me-1"></i> Submit Payment
    </button>
</div>

@if(isset($company) && $company->payment_qr_path)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex align-items-center gap-4">
        <img src="{{ asset('storage/' . $company->payment_qr_path) }}" alt="QR" style="height:100px;border-radius:8px;">
        <div>
            <h6 class="fw-semibold mb-1">Scan to Pay</h6>
            <p class="text-muted mb-0 small">Scan this QR code to make payment, then upload the screenshot below for approval.</p>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Amount</th>
                        <th>Screenshot</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerPayments as $cp)
                    <tr>
                        <td><strong>{{ $cp->invoice->invoice_number ?? '-' }}</strong></td>
                        <td class="fw-semibold">₹{{ number_format($cp->amount, 2) }}</td>
                        <td>
                            <a href="{{ asset('storage/' . $cp->screenshot) }}" target="_blank">
                                <img src="{{ asset('storage/' . $cp->screenshot) }}" alt="SS"
                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                            </a>
                        </td>
                        <td>
                            @if($cp->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($cp->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $cp->created_at->format('d M Y, h:i A') }}</td>
                        <td class="small text-muted">{{ $cp->admin_notes ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-credit-card fs-1 d-block mb-2"></i>
                            No payment submissions yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $customerPayments->links() }}
</div>

{{-- Submit Payment Modal --}}
<div class="modal fade" id="submitPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('portal.payments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-upload me-2"></i>Submit Payment Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="invoice_id" class="form-label fw-semibold">Invoice <span class="text-danger">*</span></label>
                        <select class="form-select" name="invoice_id" id="invoice_id" required>
                            <option value="">-- Select Invoice --</option>
                            @foreach($unpaidInvoices as $inv)
                                <option value="{{ $inv->id }}" data-total="{{ $inv->total }}" data-paid="{{ $inv->payments->sum('amount') }}">
                                    {{ $inv->invoice_number }} — Due: ₹{{ number_format($inv->total - $inv->payments->sum('amount'), 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-semibold">Amount Paid (₹) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="amount" id="amount" min="1" step="0.01" required placeholder="Enter amount paid">
                    </div>
                    <div class="mb-3">
                        <label for="screenshot" class="form-label fw-semibold">Payment Screenshot <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="screenshot" id="screenshot" accept="image/*" required>
                        <small class="text-muted">Upload a screenshot of your payment confirmation</small>
                        <div id="ssPreview" class="mt-2" style="display:none;">
                            <img id="ssPreviewImg" src="" alt="Preview" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #dee2e6;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Submit for Approval</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('screenshot').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('ssPreviewImg').src = ev.target.result;
            document.getElementById('ssPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('invoice_id').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    var total = parseFloat(opt.getAttribute('data-total')) || 0;
    var paid = parseFloat(opt.getAttribute('data-paid')) || 0;
    var due = total - paid;
    if (due > 0) {
        document.getElementById('amount').value = due.toFixed(2);
    }
});
</script>
@endsection
