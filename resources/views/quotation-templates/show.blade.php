@extends('layouts.app')

@section('title', $quotation_template->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $quotation_template->name }}</h4>
        <p class="text-muted mb-0">Total: ₹{{ number_format($quotation_template->total, 2) }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('estimates.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Estimates</a>
        <a href="{{ route('quotation-templates.edit', $quotation_template) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil me-1"></i>Edit</a>
        <a href="{{ route('quotation-templates.pdf', $quotation_template) }}" class="btn btn-outline-info" target="_blank"><i class="bi bi-file-pdf me-1"></i>View PDF</a>
        <a href="{{ route('quotation-templates.download', $quotation_template) }}" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Download PDF</a>
        <form action="{{ route('quotation-templates.to-estimate', $quotation_template) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-file-earmark-plus me-1"></i>Create Estimate from this</button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation_template->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->display_name }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">₹{{ number_format($item->qty * $item->unit_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Total</td>
                            <td class="text-end">₹{{ number_format($quotation_template->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Send on WhatsApp</div>
            <div class="card-body">
                <p class="small text-muted">Select a customer to open WhatsApp with their number. You can then send the quote message and attach the PDF manually.</p>
                <form id="whatsappForm" class="d-flex gap-2 flex-wrap">
                    <select class="form-select form-select-sm" id="whatsapp_customer_id" style="max-width: 220px;">
                        <option value="">-- Select customer --</option>
                        @foreach($customers as $c)
                            @php
                                $phone = $c->whatsapp ?? $c->phone;
                                $digits = $phone ? preg_replace('/\D/', '', $phone) : '';
                                $waNumber = $digits ? (strlen($digits) <= 10 ? '91' . $digits : $digits) : '';
                            @endphp
                            @if($waNumber)
                            <option value="{{ $waNumber }}" data-name="{{ $c->name }}">{{ $c->name }} ({{ $c->phone }})</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-success btn-sm" id="openWhatsApp">
                        <i class="bi bi-whatsapp me-1"></i>Open WhatsApp
                    </button>
                </form>
                @if($customers->isEmpty() || !$customers->contains(fn($c) => $c->whatsapp ?? $c->phone))
                <p class="small text-muted mt-2 mb-0">Add customer phone/WhatsApp numbers to use this feature.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.getElementById('openWhatsApp').addEventListener('click', function() {
    var sel = document.getElementById('whatsapp_customer_id');
    var number = sel.value;
    if (!number) {
        alert('Please select a customer with a phone number.');
        return;
    }
    var text = encodeURIComponent('Hi, Please find your estimate attached. Total: ₹{{ number_format($quotation_template->total, 2) }}. Thank you.');
    var url = 'https://wa.me/' + number + '?text=' + text;
    window.open(url, '_blank');
});
</script>
@endsection
