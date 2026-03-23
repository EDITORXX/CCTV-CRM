<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill of Supply — {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .bill-card { max-width: 780px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.08); overflow: hidden; }
        .bill-header { background: #1a1c2e; color: #fff; padding: 1.5rem 2rem; }
        .bill-header h1 { font-size: 1.1rem; font-weight: 600; margin: 0; }
        .bill-title { text-align: center; padding: 1rem 0 0; font-size: 1.3rem; font-weight: 700; letter-spacing: 2px; color: #1a1c2e; }
        .items-table th { background: #1a1c2e; color: #fff; font-size: .8rem; text-transform: uppercase; }
        .total-row td { font-weight: 700; font-size: 1.05rem; border-top: 2px solid #1a1c2e !important; }
        #sig-canvas { border: 2px dashed #adb5bd; border-radius: 8px; cursor: crosshair; touch-action: none; background: #f8f9fa; }
        .signed-box { background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 8px; padding: 1rem; }
        .watermark-signed { position: fixed; top: 35%; left: 15%; font-size: 5rem; color: rgba(40,167,69,.07); transform: rotate(-30deg); pointer-events: none; z-index: 0; font-weight: 900; }
    </style>
</head>
<body>

<div class="bill-card">
    {{-- Header --}}
    <div class="bill-header d-flex justify-content-between align-items-start">
        <div>
            <div style="font-size:1.5rem;font-weight:700;">{{ $company->name }}</div>
            <div style="font-size:.8rem;opacity:.7;margin-top:2px;">Complete CCTV & Security Solutions</div>
            @if($company->address)<div style="font-size:.78rem;opacity:.6;margin-top:4px;">{{ $company->address }}</div>@endif
        </div>
        <div class="text-end">
            @if($company->gstin)<div style="font-size:.8rem;">GSTIN: {{ $company->gstin }}</div>@endif
            @if($company->phone)<div style="font-size:.78rem;opacity:.7;">{{ $company->phone }}</div>@endif
        </div>
    </div>

    <div class="px-4 pb-4">
        {{-- Title --}}
        @php $hasGst = $invoice->is_gst && $invoice->gst_amount > 0; @endphp
        <div class="bill-title">{{ $hasGst ? 'TAX INVOICE' : 'BILL OF SUPPLY' }}</div>
        @if(!$hasGst)<div class="text-center text-muted small mb-2">(Without GST)</div>@endif

        {{-- Billing Info --}}
        <div class="row g-3 mt-1 mb-3">
            <div class="col-sm-6">
                <div class="border rounded p-3 h-100">
                    <div class="small fw-bold text-uppercase text-muted mb-2">Bill To</div>
                    <div class="fw-semibold">{{ $invoice->customer->name }}</div>
                    @if($invoice->customer->phone)<div class="small text-muted">{{ $invoice->customer->phone }}</div>@endif
                    @if($invoice->customer->address)<div class="small text-muted">{{ $invoice->customer->address }}</div>@endif
                    @if($invoice->site)<div class="small text-muted">Site: {{ $invoice->site->site_name }}</div>@endif
                </div>
            </div>
            <div class="col-sm-6">
                <div class="border rounded p-3 h-100">
                    <div class="small fw-bold text-uppercase text-muted mb-2">Bill Details</div>
                    <table class="w-100" style="font-size:.85rem;">
                        <tr><td class="text-muted pe-3">Bill No.</td><td class="fw-semibold">{{ $invoice->invoice_number }}</td></tr>
                        <tr><td class="text-muted">Date</td><td>{{ $invoice->invoice_date->format('d M Y') }}</td></tr>
                        @if($invoice->remaining_due_date)
                        <tr><td class="text-muted">Due Date</td><td>{{ $invoice->remaining_due_date->format('d M Y') }}</td></tr>
                        @endif
                        <tr>
                            <td class="text-muted">Status</td>
                            <td><span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : 'secondary') }}">{{ ucfirst($invoice->status) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="table-responsive">
            <table class="table items-table table-bordered mb-0" style="font-size:.85rem;">
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th>Product</th>
                        <th class="text-center" style="width:50px">Qty</th>
                        <th class="text-end" style="width:90px">Rate</th>
                        @if($hasGst)
                            <th class="text-center" style="width:60px">GST%</th>
                            <th class="text-end" style="width:80px">GST</th>
                        @endif
                        <th class="text-end" style="width:100px">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $i => $item)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>
                            <div>{{ $item->product->name }}
                                @if($item->product->brand)<span class="text-muted"> — {{ $item->product->brand }}</span>@endif
                            </div>
                            @if($item->serialNumbers->count())
                                <div class="text-muted" style="font-size:.75rem;">S/N: {{ $item->serialNumbers->pluck('serial_number')->implode(', ') }}</div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                        @if($hasGst)
                            <td class="text-center">{{ $item->gst_percent }}%</td>
                            <td class="text-end">₹{{ number_format(($item->qty * $item->unit_price) * ($item->gst_percent / 100), 2) }}</td>
                        @endif
                        <td class="text-end fw-semibold">₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary --}}
        <div class="row justify-content-end mt-3">
            <div class="col-sm-5">
                <table class="table table-borderless table-sm mb-0" style="font-size:.88rem;">
                    <tr><td class="text-muted">Subtotal</td><td class="text-end">₹{{ number_format($invoice->subtotal, 2) }}</td></tr>
                    @if($hasGst)
                        <tr><td class="text-muted">CGST</td><td class="text-end">₹{{ number_format($invoice->gst_amount/2, 2) }}</td></tr>
                        <tr><td class="text-muted">SGST</td><td class="text-end">₹{{ number_format($invoice->gst_amount/2, 2) }}</td></tr>
                    @endif
                    @if($invoice->discount > 0)
                        <tr><td class="text-muted">Discount</td><td class="text-end text-danger">-₹{{ number_format($invoice->discount, 2) }}</td></tr>
                    @endif
                    @if($totalPaid > 0)
                        <tr><td class="text-muted">Paid</td><td class="text-end text-success">-₹{{ number_format($totalPaid, 2) }}</td></tr>
                    @endif
                    <tr class="total-row">
                        <td>Grand Total</td>
                        <td class="text-end text-success">₹{{ number_format($invoice->total, 2) }}</td>
                    </tr>
                    @if($balance > 0)
                        <tr><td class="text-danger small">Balance Due</td><td class="text-end text-danger fw-bold">₹{{ number_format($balance, 2) }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        @if($invoice->notes)
            <div class="alert alert-light border mt-3 mb-0 small"><strong>Notes:</strong> {{ $invoice->notes }}</div>
        @endif

        {{-- Terms & Conditions + Signature link --}}
        <div class="mt-4 border rounded-3 p-3 text-center">
            @if($invoice->customer_signed_at)
                <div class="d-inline-flex align-items-center gap-2 text-success">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <div class="text-start">
                        <div class="fw-semibold">Terms & Conditions Signed</div>
                        <div class="small text-muted">{{ $invoice->customer_signed_at->format('d M Y, h:i A') }} | IP: {{ $invoice->customer_ip }}</div>
                    </div>
                </div>
            @else
                <i class="bi bi-file-text fs-2 text-primary d-block mb-2"></i>
                <p class="mb-3 small text-muted">Invoice accept karne ke liye Terms &amp; Conditions padhein aur digitally sign karein.</p>
                <a href="{{ route('invoice.public.terms', $token) }}" class="btn btn-primary">
                    <i class="bi bi-pen me-1"></i> Read &amp; Sign Terms &amp; Conditions
                </a>
            @endif
        </div>

        <div class="text-center text-muted mt-3" style="font-size:.75rem;">
            This is a digitally generated document. For queries contact {{ $company->phone ?? $company->email ?? '' }}.
        </div>
    </div>
</div>

@if($invoice->customer_signed_at)
<div class="watermark-signed">SIGNED</div>
@endif

</body>
</html>
