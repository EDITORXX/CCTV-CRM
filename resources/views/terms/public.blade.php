<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms &amp; Conditions — {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .bill-card { max-width: 700px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.08); overflow: hidden; }
        .bill-header { background: #1a1c2e; color: #fff; padding: 1.2rem 1.75rem; }
        #sig-canvas { border: 2px dashed #adb5bd; border-radius: 8px; cursor: crosshair; touch-action: none; background: #f8f9fa; }
        .signed-box { background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 8px; padding: 1rem; }
        .lang-btn.active { font-weight: 700; }
        .terms-list li { margin-bottom: .35rem; }
        .watermark-signed { position: fixed; top: 35%; left: 15%; font-size: 5rem; color: rgba(40,167,69,.06); transform: rotate(-30deg); pointer-events: none; z-index: 0; font-weight: 900; }
    </style>
</head>
<body>

<div class="bill-card">
    {{-- Header --}}
    <div class="bill-header d-flex justify-content-between align-items-start">
        <div>
            <div style="font-size:1.3rem;font-weight:700;">{{ $company->name }}</div>
            <div style="font-size:.78rem;opacity:.7;margin-top:2px;">Complete CCTV &amp; Security Solutions</div>
        </div>
        <div class="text-end" style="font-size:.78rem;">
            @if($company->gstin)<div>GSTIN: {{ $company->gstin }}</div>@endif
            @if($company->phone)<div style="opacity:.7;">{{ $company->phone }}</div>@endif
        </div>
    </div>

    <div class="px-4 pb-4 pt-3">

        {{-- Invoice reference --}}
        <div class="alert alert-light border mb-3 py-2 small">
            <i class="bi bi-receipt me-1"></i>
            Reference: <strong>{{ $invoice->invoice_number }}</strong>
            &nbsp;|&nbsp; {{ $invoice->customer->name }}
            &nbsp;|&nbsp; {{ $invoice->invoice_date->format('d M Y') }}
            &nbsp;|&nbsp; <a href="{{ route('invoice.public.show', $token) }}" class="text-decoration-none">View Invoice</a>
        </div>

        {{-- Title + Language toggle --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-file-text me-1"></i>Terms &amp; Conditions</h5>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-primary lang-btn active" id="btn-en" onclick="switchLang('en')">English</button>
                <button type="button" class="btn btn-outline-warning lang-btn" id="btn-hi" onclick="switchLang('hi')">हिंदी</button>
            </div>
        </div>

        {{-- English Terms --}}
        <div id="terms-en">
            @php
                $defaultEn = [
                    'Goods once sold will not be taken back.',
                    'Warranty is subject to the terms mentioned in the warranty card.',
                    'Payment is due within 15 days from the date of invoice.',
                    'All disputes are subject to local jurisdiction.',
                ];
                $termsEn = $company->invoice_terms
                    ? array_filter(array_map('trim', explode("\n", $company->invoice_terms)))
                    : $defaultEn;
            @endphp
            <ol class="terms-list">
                @foreach($termsEn as $line)
                    <li>{{ preg_replace('/^\d+[\.\)।]?\s*/', '', $line) }}</li>
                @endforeach
            </ol>
        </div>

        {{-- Hindi Terms --}}
        <div id="terms-hi" class="d-none">
            @php
                $defaultHi = [
                    'बेचा गया सामान वापस नहीं लिया जाएगा।',
                    'वारंटी वारंटी कार्ड में उल्लिखित शर्तों के अनुसार होगी।',
                    'चालान की तारीख से 15 दिनों के भीतर भुगतान करें।',
                    'सभी विवाद स्थानीय न्यायालय के अधीन होंगे।',
                ];
                $termsHi = $company->invoice_terms_hi
                    ? array_filter(array_map('trim', explode("\n", $company->invoice_terms_hi)))
                    : $defaultHi;
            @endphp
            <ol class="terms-list">
                @foreach($termsHi as $line)
                    <li>{{ preg_replace('/^[०-९\d]+[\.\)।]?\s*/', '', $line) }}</li>
                @endforeach
            </ol>
        </div>

        <hr class="my-4">

        {{-- Digital Signature Section --}}
        <div id="sig-section">
            @if($invoice->customer_signed_at)
                <div class="signed-box">
                    <div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Digitally Signed &amp; Accepted</strong></div>
                    <img src="{{ $invoice->customer_signature }}" alt="Customer Signature"
                         style="max-height:80px;border:1px solid #ccc;border-radius:6px;background:#fff;padding:4px;">
                    <div class="mt-2 small text-muted">
                        <i class="bi bi-clock me-1"></i> {{ $invoice->customer_signed_at->format('d M Y, h:i A') }}
                        &nbsp;|&nbsp;
                        <i class="bi bi-globe me-1"></i> IP: {{ $invoice->customer_ip }}
                    </div>
                    <div class="mt-1 small text-muted">
                        <i class="bi bi-receipt me-1"></i> Invoice: {{ $invoice->invoice_number }} &nbsp;|&nbsp; {{ $invoice->customer->name }}
                    </div>
                </div>
            @else
                <div class="fw-semibold mb-1"><i class="bi bi-pen me-1"></i> Sign &amp; Accept These Terms</div>
                <p class="small text-muted mb-2">Neeche sign karein (mouse ya touch se) aur "Sign &amp; Accept" dabayein — ye invoice ke saath record hoga.</p>
                <canvas id="sig-canvas" width="620" height="150" style="width:100%;max-width:620px;height:150px;"></canvas>
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="sig-clear">
                        <i class="bi bi-eraser me-1"></i> Clear
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="sig-submit">
                        <i class="bi bi-pen me-1"></i> Sign &amp; Accept
                    </button>
                </div>
                <div id="sig-result" class="mt-2"></div>
            @endif
        </div>

        <div class="text-center text-muted mt-4" style="font-size:.72rem;">
            This is a digitally generated document. For queries contact {{ $company->phone ?? $company->email ?? '' }}.
        </div>
    </div>
</div>

@if($invoice->customer_signed_at)
<div class="watermark-signed">SIGNED</div>
@endif

<script>
function switchLang(lang) {
    document.getElementById('terms-en').classList.toggle('d-none', lang !== 'en');
    document.getElementById('terms-hi').classList.toggle('d-none', lang !== 'hi');
    document.getElementById('btn-en').classList.toggle('active', lang === 'en');
    document.getElementById('btn-hi').classList.toggle('active', lang === 'hi');
}

(function() {
    var canvas = document.getElementById('sig-canvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var drawing = false, hasDrawn = false;

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width, scaleY = canvas.height / rect.height;
        var clientX = e.touches ? e.touches[0].clientX : e.clientX;
        var clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
    }

    ctx.strokeStyle = '#1a1c2e'; ctx.lineWidth = 2.5; ctx.lineCap = 'round'; ctx.lineJoin = 'round';

    canvas.addEventListener('mousedown',  function(e) { drawing = true; var p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove',  function(e) { if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasDrawn = true; });
    canvas.addEventListener('mouseup',    function()  { drawing = false; });
    canvas.addEventListener('mouseleave', function()  { drawing = false; });
    canvas.addEventListener('touchstart', function(e) { e.preventDefault(); drawing = true; var p = getPos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); }, {passive:false});
    canvas.addEventListener('touchmove',  function(e) { e.preventDefault(); if (!drawing) return; var p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hasDrawn = true; }, {passive:false});
    canvas.addEventListener('touchend',   function()  { drawing = false; });

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
                    '<div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Signed &amp; Accepted — Shukriya!</strong></div>' +
                    '<div class="small text-muted mt-1"><i class="bi bi-clock me-1"></i> ' + data.signed_at +
                    ' &nbsp;|&nbsp; <i class="bi bi-globe me-1"></i> IP: ' + data.ip + '</div>' +
                    '</div>';
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-pen me-1"></i> Sign &amp; Accept';
                document.getElementById('sig-result').innerHTML = '<div class="alert alert-danger py-1 small">Error. Try again.</div>';
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-pen me-1"></i> Sign &amp; Accept';
            document.getElementById('sig-result').innerHTML = '<div class="alert alert-danger py-1 small">Network error. Try again.</div>';
        });
    });
})();
</script>
</body>
</html>
