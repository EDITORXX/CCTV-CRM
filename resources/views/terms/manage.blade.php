@extends('layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-file-text me-2"></i>Terms &amp; Conditions</h4>
        <p class="text-muted mb-0">Invoice / Bill of Supply ke liye T&C manage karein (English + Hindi)</p>
    </div>
    @if($company->share_token ?? false)
    <a href="{{ url('/terms/' . $company->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-eye me-1"></i> Preview Public Page
    </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('terms.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- English --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <span class="badge bg-primary">EN</span>
                    <h6 class="mb-0 fw-semibold">English Terms &amp; Conditions</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Har line ek alag point banega. Blank chhodo to default terms use honge.</p>
                    <textarea class="form-control font-monospace @error('invoice_terms') is-invalid @enderror"
                              id="invoice_terms" name="invoice_terms" rows="14"
                              placeholder="1. Goods once sold will not be taken back.&#10;2. Warranty as per warranty card.&#10;3. Payment due within 15 days.&#10;4. All disputes subject to local jurisdiction.">{{ old('invoice_terms', $company->invoice_terms) }}</textarea>
                    @error('invoice_terms')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle me-1"></i> Ek line = ek point. Enter dabao naya point shuru karne ke liye.
                    </small>
                </div>
            </div>
        </div>

        {{-- Hindi --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark">HI</span>
                    <h6 class="mb-0 fw-semibold">Hindi Terms &amp; Conditions (हिंदी)</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Customer Hindi mein padhna chahein to ye dikhega. Blank chhodo to English use hoga.</p>
                    <textarea class="form-control @error('invoice_terms_hi') is-invalid @enderror"
                              id="invoice_terms_hi" name="invoice_terms_hi" rows="14"
                              placeholder="१. बेचा गया सामान वापस नहीं लिया जाएगा।&#10;२. वारंटी वारंटी कार्ड के अनुसार होगी।&#10;३. भुगतान 15 दिनों के भीतर करें।&#10;४. सभी विवाद स्थानीय न्यायालय के अधीन होंगे।">{{ old('invoice_terms_hi', $company->invoice_terms_hi) }}</textarea>
                    @error('invoice_terms_hi')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle me-1"></i> Customer public T&C page pe English/Hindi switch kar sakta hai.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview box --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold"><i class="bi bi-eye me-2 text-primary"></i>Live Preview</h6>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-outline-primary btn-sm" id="prevLangEn">English</button>
                <button type="button" class="btn btn-outline-warning btn-sm" id="prevLangHi">हिंदी</button>
            </div>
            <div id="preview-en" class="preview-box">
                <strong class="text-muted small">Terms &amp; Conditions:</strong>
                <ol id="prev-list-en" class="mt-1 mb-0 small"></ol>
            </div>
            <div id="preview-hi" class="preview-box d-none">
                <strong class="text-muted small">नियम एवं शर्तें:</strong>
                <ol id="prev-list-hi" class="mt-1 mb-0 small"></ol>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4 mb-4">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Save Terms &amp; Conditions
        </button>
        <a href="{{ route('company.settings') }}" class="btn btn-outline-secondary">Company Settings</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
function buildPreview(textareaId, listId) {
    var val = document.getElementById(textareaId).value.trim();
    var lines = val ? val.split('\n').map(l => l.replace(/^\d+[\.\)।]?\s*/, '').trim()).filter(Boolean) : [];
    var list = document.getElementById(listId);
    if (lines.length) {
        list.innerHTML = lines.map(l => '<li>' + l.replace(/</g,'&lt;') + '</li>').join('');
    } else {
        list.innerHTML = '<li class="text-muted">No terms added — default terms will be used.</li>';
    }
}
function refreshPreview() {
    buildPreview('invoice_terms', 'prev-list-en');
    buildPreview('invoice_terms_hi', 'prev-list-hi');
}
refreshPreview();
document.getElementById('invoice_terms').addEventListener('input', refreshPreview);
document.getElementById('invoice_terms_hi').addEventListener('input', refreshPreview);

document.getElementById('prevLangEn').addEventListener('click', function() {
    document.getElementById('preview-en').classList.remove('d-none');
    document.getElementById('preview-hi').classList.add('d-none');
});
document.getElementById('prevLangHi').addEventListener('click', function() {
    document.getElementById('preview-hi').classList.remove('d-none');
    document.getElementById('preview-en').classList.add('d-none');
});
</script>
@endsection
