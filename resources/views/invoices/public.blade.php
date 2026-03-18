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
        <div class="bill-title">{{ $invoice->is_gst ? 'TAX INVOICE' : 'BILL OF SUPPLY' }}</div>
        @if(!$invoice->is_gst)<div class="text-center text-muted small mb-2">(Without GST)</div>@endif

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
                        @if($invoice->is_gst)
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
                        @if($invoice->is_gst)
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
                    @if($invoice->is_gst)
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

        {{-- Digital Signature Section --}}
        <div class="mt-4 border rounded-3 p-3" id="sig-section">
            <div class="fw-semibold mb-1"><i class="bi bi-pen me-1"></i> Digital Signature</div>

            @if($invoice->customer_signed_at)
                {{-- Already signed --}}
                <div class="signed-box">
                    <div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Digitally Signed</strong></div>
                    <img src="{{ $invoice->customer_signature }}" alt="Customer Signature" style="max-height:80px;border:1px solid #ccc;border-radius:6px;background:#fff;padding:4px;">
                    <div class="mt-2 small text-muted">
                        <i class="bi bi-clock me-1"></i> {{ $invoice->customer_signed_at->format('d M Y, h:i A') }}
                        &nbsp;|&nbsp;
                        <i class="bi bi-globe me-1"></i> IP: {{ $invoice->customer_ip }}
                    </div>
                </div>
            @else
                {{-- Signature pad --}}
                <p class="small text-muted mb-2">Neeche sign karein (mouse ya touch se) aur "Sign & Accept" dabayein.</p>
                <canvas id="sig-canvas" width="680" height="150" style="width:100%;max-width:680px;height:150px;"></canvas>
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="sig-clear">
                        <i class="bi bi-eraser me-1"></i> Clear
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="sig-submit">
                        <i class="bi bi-pen me-1"></i> Sign & Accept
                    </button>
                </div>
                <div id="sig-result" class="mt-2"></div>
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

<script>
(function() {
    var canvas = document.getElementById('sig-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var drawing = false;
    var hasDrawn = false;

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        var clientX = e.touches ? e.touches[0].clientX : e.clientX;
        var clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
    }

    ctx.strokeStyle = '#1a1c2e';
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    canvas.addEventListener('mousedown', function(e) { drawing = true; var p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove', function(e) { if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasDrawn = true; });
    canvas.addEventListener('mouseup', function() { drawing = false; });
    canvas.addEventListener('mouseleave', function() { drawing = false; });
    canvas.addEventListener('touchstart', function(e) { e.preventDefault(); drawing = true; var p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }, {passive:false});
    canvas.addEventListener('touchmove', function(e) { e.preventDefault(); if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasDrawn = true; }, {passive:false});
    canvas.addEventListener('touchend', function() { drawing = false; });

    document.getElementById('sig-clear').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
        document.getElementById('sig-result').innerHTML = '';
    });

    document.getElementById('sig-submit').addEventListener('click', function() {
        if (!hasDrawn) {
            document.getElementById('sig-result').innerHTML = '<div class="alert alert-warning py-1 small">Pehle sign karein.</div>';
            return;
        }
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        fetch('{{ route("invoice.public.sign", $token) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ signature: canvas.toDataURL('image/png') })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('sig-section').innerHTML =
                    '<div class="signed-box">' +
                    '<div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Digitally Signed — Shukriya!</strong></div>' +
                    '<div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i> ' + data.signed_at +
                    ' &nbsp;|&nbsp; <i class="bi bi-globe me-1"></i> IP: ' + data.ip + '</div>' +
                    '</div>';
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-pen me-1"></i> Sign & Accept';
                document.getElementById('sig-result').innerHTML = '<div class="alert alert-danger py-1 small">Error. Try again.</div>';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-pen me-1"></i> Sign & Accept';
            document.getElementById('sig-result').innerHTML = '<div class="alert alert-danger py-1 small">Network error. Try again.</div>';
        });
    });
})();
</script>
</body>
</html>
