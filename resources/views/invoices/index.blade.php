@extends('layouts.app')

@section('title', 'Invoices')

@section('styles')
<style>
    /* ── Action buttons ── */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        text-decoration: none;
        font-size: .9rem;
        transition: all .15s ease;
        cursor: pointer;
    }
    .action-btn:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,.15); }
    .action-btn.view   { background: #e0f2fe; color: #0284c7; }
    .action-btn.view:hover { background: #0284c7; color: #fff; }
    .action-btn.pdf    { background: #fef3c7; color: #d97706; }
    .action-btn.pdf:hover { background: #d97706; color: #fff; }
    .action-btn.share  { background: #dcfce7; color: #16a34a; }
    .action-btn.share:hover { background: #16a34a; color: #fff; }
    .action-btn.edit   { background: #ede9fe; color: #7c3aed; }
    .action-btn.edit:hover { background: #7c3aed; color: #fff; }
    .action-btn.del    { background: #fee2e2; color: #dc2626; }
    .action-btn.del:hover { background: #dc2626; color: #fff; }

    /* Mobile action strip */
    .mobile-actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .mobile-actions .action-btn { width: 36px; height: 36px; font-size: 1rem; border-radius: 10px; }

    /* Profit badge */
    .profit-positive { color: #16a34a; font-weight: 600; }
    .profit-negative { color: #dc2626; font-weight: 600; }
    .profit-zero     { color: #6b7280; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Invoices</h4>
        <p class="text-muted mb-0">Manage sales invoices</p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Invoice
    </a>
</div>

{{-- Desktop table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="invoicesTable">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="ps-3">#</th>
                        <th>Invoice</th>
                        <th>Customer / Site</th>
                        <th>Date</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Profit</th>
                        <th class="text-center">Status</th>
                        <th width="160" class="text-center pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    @php
                        $expenseTotal = $invoice->invoiceExpenses->sum('amount');
                        $profit = $invoice->subtotal - $expenseTotal;
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">
                                {{ $invoice->invoice_number }}
                            </a>
                            @if($invoice->is_gst)
                                <span class="badge bg-success bg-opacity-10 text-success ms-1" style="font-size:.65rem;">GST</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium">{{ $invoice->customer->name ?? '—' }}</div>
                            @if($invoice->site)
                                <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $invoice->site->name }}</div>
                            @endif
                        </td>
                        <td class="text-muted small">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                        <td class="text-end fw-semibold">₹{{ number_format($invoice->total, 2) }}</td>
                        <td class="text-end">
                            @if($invoice->subtotal > 0)
                                <span class="{{ $profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                                    {{ $profit >= 0 ? '' : '-' }}₹{{ number_format(abs($profit), 2) }}
                                </span>
                                @if($expenseTotal > 0)
                                    <div class="small text-muted" style="font-size:.68rem;">exp: ₹{{ number_format($expenseTotal, 2) }}</div>
                                @endif
                            @else
                                <span class="profit-zero">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @switch($invoice->status)
                                @case('draft')    <span class="badge bg-secondary">Draft</span> @break
                                @case('sent')     <span class="badge bg-primary">Sent</span> @break
                                @case('paid')     <span class="badge bg-success">Paid</span> @break
                                @case('partial')  <span class="badge bg-warning text-dark">Partial</span> @break
                                @case('cancelled')<span class="badge bg-danger">Cancelled</span> @break
                                @default          <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                            @endswitch
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('invoices.show', $invoice) }}" class="action-btn view" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="action-btn pdf" title="PDF" target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                <button type="button" class="action-btn share share-btn" title="Share"
                                    data-token="{{ $invoice->share_token }}"
                                    data-token-url="{{ route('invoices.share-token', $invoice) }}"
                                    data-csrf="{{ csrf_token() }}">
                                    <i class="bi bi-share"></i>
                                </button>
                                <a href="{{ route('invoices.edit', $invoice) }}" class="action-btn edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                                      onsubmit="return confirm('Delete this invoice?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn del" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Mobile card view --}}
<div class="d-md-none">
    @forelse($invoices as $invoice)
    @php
        $expenseTotal = $invoice->invoiceExpenses->sum('amount');
        $profit = $invoice->subtotal - $expenseTotal;
    @endphp
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">

            {{-- Top row: invoice number + total --}}
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div>
                    <a href="{{ route('invoices.show', $invoice) }}" class="fw-bold text-decoration-none fs-6">
                        {{ $invoice->invoice_number }}
                    </a>
                    <div class="text-muted small">{{ $invoice->customer->name ?? '—' }}</div>
                    @if($invoice->site)
                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $invoice->site->name }}</div>
                    @endif
                </div>
                <div class="text-end ms-2 flex-shrink-0">
                    <div class="fw-bold">₹{{ number_format($invoice->total, 2) }}</div>
                    @if($invoice->subtotal > 0)
                        <div class="small {{ $profit >= 0 ? 'profit-positive' : 'profit-negative' }}">
                            P: {{ $profit >= 0 ? '' : '-' }}₹{{ number_format(abs($profit), 2) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Status + date row --}}
            <div class="d-flex align-items-center gap-2 mb-2">
                @switch($invoice->status)
                    @case('draft')    <span class="badge bg-secondary">Draft</span> @break
                    @case('sent')     <span class="badge bg-primary">Sent</span> @break
                    @case('paid')     <span class="badge bg-success">Paid</span> @break
                    @case('partial')  <span class="badge bg-warning text-dark">Partial</span> @break
                    @case('cancelled')<span class="badge bg-danger">Cancelled</span> @break
                    @default          <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                @endswitch
                @if($invoice->is_gst)
                    <span class="badge bg-success bg-opacity-10 text-success border">GST</span>
                @endif
                <span class="text-muted small ms-auto">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</span>
            </div>

            {{-- Action buttons --}}
            <div class="border-top pt-2 mt-1">
                <div class="mobile-actions">
                    <a href="{{ route('invoices.show', $invoice) }}" class="action-btn view" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="action-btn pdf" title="PDF" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                    <button type="button" class="action-btn share share-btn" title="Share"
                        data-token="{{ $invoice->share_token }}"
                        data-token-url="{{ route('invoices.share-token', $invoice) }}"
                        data-csrf="{{ csrf_token() }}">
                        <i class="bi bi-share"></i>
                    </button>
                    <a href="{{ route('invoices.edit', $invoice) }}" class="action-btn edit" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                          onsubmit="return confirm('Delete this invoice?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn del" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-receipt fs-1 d-block mb-2"></i>
            <p>No invoices found. <a href="{{ route('invoices.create') }}">Create your first invoice</a>.</p>
        </div>
    </div>
    @endforelse
    <div class="d-flex justify-content-end mt-3">
        {{ $invoices->withQueryString()->links() }}
    </div>
</div>
@endsection

{{-- Share Link Modal --}}
<div class="modal fade" id="shareLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-share me-1"></i> Share Bill of Supply</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">Customer ko ye link bhejein — bina login ke bill dekh aur sign kar sakte hain.</p>
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" id="shareLinkInput" readonly>
                    <button class="btn btn-outline-secondary btn-sm" id="copyLinkBtn" type="button">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <div id="copyMsg" class="d-none text-success small mt-1"><i class="bi bi-check-circle me-1"></i> Copied!</div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.querySelectorAll('.share-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var existingToken = this.dataset.token;
            var tokenUrl = this.dataset.tokenUrl;
            var csrf = this.dataset.csrf;
            var self = this;

            function showModal(token) {
                document.getElementById('shareLinkInput').value = window.location.origin + '/bill/' + token;
                document.getElementById('copyMsg').classList.add('d-none');
                new bootstrap.Modal(document.getElementById('shareLinkModal')).show();
            }

            if (existingToken) { showModal(existingToken); return; }

            self.disabled = true;
            self.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            fetch(tokenUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                self.dataset.token = data.token;
                self.disabled = false;
                self.innerHTML = '<i class="bi bi-share"></i>';
                showModal(data.token);
            })
            .catch(() => {
                self.disabled = false;
                self.innerHTML = '<i class="bi bi-share"></i>';
            });
        });
    });

    document.getElementById('copyLinkBtn').addEventListener('click', function() {
        var val = document.getElementById('shareLinkInput').value;
        navigator.clipboard.writeText(val).then(function() {
            var msg = document.getElementById('copyMsg');
            msg.classList.remove('d-none');
            setTimeout(function() { msg.classList.add('d-none'); }, 2000);
        });
    });

    $(document).ready(function() {
        $('#invoicesTable').DataTable({
            paging: true,
            pageLength: 25,
            order: [[3, 'desc']],
            columnDefs: [
                { orderable: false, targets: [7] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-receipt fs-1 d-block mb-2"></i>No invoices found.</div>'
            }
        });
    });
</script>
@endsection
