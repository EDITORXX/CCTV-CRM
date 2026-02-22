@extends('layouts.app')

@section('title', 'New Estimate')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">New Estimate</h4>
        <p class="text-muted mb-0">Create a quotation for a customer (no stock check)</p>
    </div>
    <a href="{{ route('estimates.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<form action="{{ route('estimates.store') }}" method="POST" id="estimateForm">
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-info-circle me-1"></i> Estimate Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} -- {{ $customer->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="site_id" class="form-label">Site</label>
                    <select class="form-select" id="site_id" name="site_id">
                        <option value="">-- Select Customer First --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="estimate_number" class="form-label">Estimate Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estimate_number" name="estimate_number"
                           value="{{ old('estimate_number', $nextNumber) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="estimate_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="estimate_date" name="estimate_date"
                           value="{{ old('estimate_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3">
                    <label for="valid_until" class="form-label">Valid Until</label>
                    <input type="date" class="form-control" id="valid_until" name="valid_until"
                           value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_gst" name="is_gst" value="1"
                               {{ old('is_gst', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_gst">GST Estimate</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-list-ul me-1"></i> Line Items</span>
            <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="220">Product</th>
                            <th width="70">Qty</th>
                            <th width="110">Unit Price</th>
                            <th width="80" class="gst-col">GST%</th>
                            <th width="100">Discount</th>
                            <th width="90">Warranty (Mo)</th>
                            <th width="120">Line Total</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <label for="discount" class="form-label">Overall Discount (₹)</label>
                    <input type="number" class="form-control" id="discount" name="discount" min="0" step="0.01" value="{{ old('discount', 0) }}">
                    <label for="notes" class="form-label mt-3">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Terms, validity notes...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-calculator me-1"></i> Summary</div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td class="text-muted">Subtotal</td><td class="text-end fw-semibold" id="summarySubtotal">₹0.00</td></tr>
                        <tr class="gst-row"><td class="text-muted">GST Amount</td><td class="text-end fw-semibold" id="summaryGst">₹0.00</td></tr>
                        <tr><td class="text-muted">Discount</td><td class="text-end fw-semibold text-danger" id="summaryDiscount">-₹0.00</td></tr>
                        <tr class="border-top"><td class="fw-bold fs-5">Grand Total</td><td class="text-end fw-bold fs-5 text-success" id="summaryGrandTotal">₹0.00</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-lg me-1"></i> Create Estimate
        </button>
        <a href="{{ route('estimates.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var rowIndex = 0;
    @php
        $productList = $products->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sale_price' => $p->sale_price,
                'warranty_months' => $p->warranty_months,
            ];
        });
    @endphp
    var products = @json($productList);

    function buildProductOptions() {
        var opts = '<option value="">-- Select Product --</option>';
        products.forEach(function(p) {
            opts += '<option value="' + p.id + '" data-price="' + (p.sale_price || 0) + '" data-warranty="' + (p.warranty_months || 0) + '">' + p.name + '</option>';
        });
        return opts;
    }

    function addItemRow() {
        var gstDisplay = $('#is_gst').is(':checked') ? '' : 'style="display:none"';
        var html = '<tr data-row="' + rowIndex + '">' +
            '<td><select class="form-select form-select-sm item-product" name="items[' + rowIndex + '][product_id]" required>' + buildProductOptions() + '</select></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" name="items[' + rowIndex + '][qty]" min="1" value="1" required></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" name="items[' + rowIndex + '][unit_price]" min="0" step="0.01" value="0" required></td>' +
            '<td class="gst-col" ' + gstDisplay + '><input type="number" class="form-control form-control-sm item-gst" name="items[' + rowIndex + '][gst_percent]" min="0" max="100" step="0.01" value="18"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-discount" name="items[' + rowIndex + '][discount]" min="0" step="0.01" value="0"></td>' +
            '<td><input type="number" class="form-control form-control-sm" name="items[' + rowIndex + '][warranty_months]" min="0" value="0"></td>' +
            '<td class="line-total fw-semibold">₹0.00</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x-lg"></i></button></td>' +
            '</tr>';
        $('#itemsBody').append(html);
        rowIndex++;
    }

    function calculateTotals() {
        var subtotal = 0, gstTotal = 0;
        var isGst = $('#is_gst').is(':checked');
        $('#itemsBody tr').each(function() {
            var qty = parseFloat($(this).find('.item-qty').val()) || 0;
            var price = parseFloat($(this).find('.item-price').val()) || 0;
            var gstPct = isGst ? (parseFloat($(this).find('.item-gst').val()) || 0) : 0;
            var disc = parseFloat($(this).find('.item-discount').val()) || 0;
            var base = qty * price;
            var gstAmt = base * (gstPct / 100);
            var lineTotal = base + gstAmt - disc;
            $(this).find('.line-total').text('₹' + lineTotal.toFixed(2));
            subtotal += base;
            gstTotal += gstAmt;
        });
        var overallDiscount = parseFloat($('#discount').val()) || 0;
        var grandTotal = subtotal + gstTotal - overallDiscount;
        $('#summarySubtotal').text('₹' + subtotal.toFixed(2));
        $('#summaryGst').text('₹' + gstTotal.toFixed(2));
        $('#summaryDiscount').text('-₹' + overallDiscount.toFixed(2));
        $('#summaryGrandTotal').text('₹' + grandTotal.toFixed(2));
    }

    $('#customer_id').on('change', function() {
        var customerId = $(this).val();
        var $siteSelect = $('#site_id');
        $siteSelect.html('<option value="">-- Loading... --</option>');
        if (!customerId) { $siteSelect.html('<option value="">-- Select Customer First --</option>'); return; }
        var url = "{{ route('api.customer.sites', ':id') }}".replace(':id', customerId);
        $.get(url, function(data) {
            var opts = '<option value="">-- Select Site --</option>';
            $.each(data, function(i, site) { opts += '<option value="' + site.id + '">' + site.site_name + '</option>'; });
            $siteSelect.html(opts);
        });
    });

    $(document).on('change', '.item-product', function() {
        var $row = $(this).closest('tr');
        var $opt = $(this).find(':selected');
        var price = $opt.data('price') || 0;
        var warranty = $opt.data('warranty') || 0;
        $row.find('.item-price').val(price);
        $row.find('[name*="warranty_months"]').val(warranty);
        calculateTotals();
    });

    $('#is_gst').on('change', function() {
        if ($(this).is(':checked')) { $('.gst-col').show(); $('.gst-row').show(); }
        else { $('.gst-col').hide(); $('.gst-row').hide(); }
        calculateTotals();
    });

    $('#addItemBtn').on('click', function() { addItemRow(); });
    $(document).on('click', '.remove-item', function() { $(this).closest('tr').remove(); calculateTotals(); });
    $(document).on('input', '.item-qty, .item-price, .item-gst, .item-discount, #discount', function() { calculateTotals(); });

    $('#is_gst').trigger('change');
    addItemRow();
});
</script>
@endsection
