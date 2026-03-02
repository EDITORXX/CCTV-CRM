@extends('layouts.app')

@section('title', 'New Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">New Invoice</h4>
        <p class="text-muted mb-0">Create a sales invoice for a customer</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    {{-- Header --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-info-circle me-1"></i> Invoice Details
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
                    @error('customer_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="site_id" class="form-label">Site <span class="text-danger">*</span></label>
                    <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id" required>
                        <option value="">-- Select Customer First --</option>
                    </select>
                    @error('site_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('invoice_number') is-invalid @enderror"
                           id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $nextNumber ?? $nextInvoiceNumber ?? '') }}" required>
                    @error('invoice_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
                           id="invoice_date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    @error('invoice_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_gst" name="is_gst" value="1"
                               {{ old('is_gst', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_gst">GST Invoice</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-list-ul me-1"></i> Line Items</span>
            <button type="button" class="btn btn-sm btn-success" id="addItemBtn" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body" id="itemsList">
            <p class="text-muted text-center mb-0" id="noItemsMsg">
                <i class="bi bi-inbox me-1"></i> No items added yet. Click "Add Item" to start.
            </p>
        </div>
    </div>

    {{-- Add Item Modal --}}
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel"><i class="bi bi-plus-circle me-1"></i> Add Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="modalProductId">
                            <option value="">-- Select Product --</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modalPrice" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Qty <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modalQty" min="1" value="1">
                        </div>
                        <div class="col-6 modal-gst-field">
                            <label class="form-label fw-semibold">GST %</label>
                            <input type="number" class="form-control" id="modalGst" min="0" max="100" step="0.01" value="18">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Discount (₹)</label>
                            <input type="number" class="form-control" id="modalDiscount" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Warranty (Months)</label>
                            <input type="number" class="form-control" id="modalWarranty" min="0" value="12">
                        </div>
                    </div>

                    <div class="mt-3" id="modalSerialsGroup" style="display:none;">
                        <label class="form-label fw-semibold">Serial Numbers</label>
                        <input type="text" class="form-control" id="modalSerials" placeholder="Comma-separated serial IDs">
                        <div class="form-text" id="modalSerialsHint"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="modalAddBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add to Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <label for="discount" class="form-label">Overall Discount (₹)</label>
                    <input type="number" class="form-control @error('discount') is-invalid @enderror"
                           id="discount" name="discount" min="0" step="0.01" value="{{ old('discount', 0) }}">
                    @error('discount')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <label for="notes" class="form-label mt-3">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="3" placeholder="Payment terms, delivery notes...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-calculator me-1"></i> Summary
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted">Subtotal</td>
                            <td class="text-end fw-semibold" id="summarySubtotal">₹0.00</td>
                        </tr>
                        <tr class="gst-row">
                            <td class="text-muted">GST Amount</td>
                            <td class="text-end fw-semibold" id="summaryGst">₹0.00</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Discount</td>
                            <td class="text-end fw-semibold text-danger" id="summaryDiscount">-₹0.00</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold fs-5">Grand Total</td>
                            <td class="text-end fw-bold fs-5 text-success" id="summaryGrandTotal">₹0.00</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-lg me-1"></i> Create Invoice
        </button>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
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
                'track_serial' => $p->track_serial ?? false,
            ];
        });
    @endphp
    var products = @json($productList);

    function populateModalProducts() {
        var opts = '<option value="">-- Select Product --</option>';
        products.forEach(function(p) {
            opts += '<option value="' + p.id + '" data-price="' + (p.sale_price || 0) + '" data-warranty="' + (p.warranty_months || 0) + '" data-serial="' + (p.track_serial ? '1' : '0') + '">' + p.name + '</option>';
        });
        $('#modalProductId').html(opts);
    }

    function resetModal() {
        $('#modalProductId').val('').removeClass('is-invalid');
        $('#modalPrice').val(0);
        $('#modalQty').val(1);
        $('#modalGst').val(18);
        $('#modalDiscount').val(0);
        $('#modalWarranty').val(12);
        $('#modalSerials').val('');
        $('#modalSerialsGroup').hide();
        $('#modalSerialsHint').text('');
        populateModalProducts();
        if ($('#is_gst').is(':checked')) { $('.modal-gst-field').show(); }
        else { $('.modal-gst-field').hide(); }
    }

    function addItemCard(data) {
        var isGst = $('#is_gst').is(':checked');
        var gstPct = isGst ? (data.gst_percent || 0) : 0;
        var base = data.qty * data.unit_price;
        var gstAmt = base * (gstPct / 100);
        var lineTotal = base + gstAmt - (data.discount || 0);
        var displayName = data.product_name || '--';
        var serialsDisplay = data.serial_ids ? '<span class="badge bg-info bg-opacity-10 text-info border"><i class="bi bi-upc-scan"></i> Serials</span>' : '';

        var html = '<div class="item-card border rounded-3 p-3 mb-2 position-relative" data-row="' + rowIndex + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][product_id]" value="' + (data.product_id || '') + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][qty]" value="' + data.qty + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][unit_price]" value="' + data.unit_price + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][gst_percent]" value="' + gstPct + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][discount]" value="' + (data.discount || 0) + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][warranty_months]" value="' + (data.warranty_months || 0) + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][serial_ids]" value="' + (data.serial_ids || '') + '">' +
            '<button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 remove-item" style="z-index:1;"><i class="bi bi-x-lg"></i></button>' +
            '<div class="fw-semibold mb-1" style="padding-right:2rem;">' + $('<span>').text(displayName).html() + '</div>' +
            '<div class="d-flex flex-wrap gap-2 small">' +
                '<span class="badge bg-light text-dark border">Qty: ' + data.qty + '</span>' +
                '<span class="badge bg-light text-dark border">₹' + parseFloat(data.unit_price).toFixed(0) + '</span>' +
                (isGst ? '<span class="badge bg-light text-dark border">' + gstPct + '% GST</span>' : '') +
                (data.discount > 0 ? '<span class="badge bg-danger bg-opacity-10 text-danger border">-₹' + parseFloat(data.discount).toFixed(0) + '</span>' : '') +
                '<span class="badge bg-light text-dark border"><i class="bi bi-shield-check"></i> ' + (data.warranty_months || 0) + ' Mo</span>' +
                serialsDisplay +
            '</div>' +
            '<div class="text-end fw-bold text-success mt-1">₹' + lineTotal.toFixed(2) + '</div>' +
        '</div>';

        $('#itemsList').append(html);
        $('#noItemsMsg').hide();
        rowIndex++;
        calculateTotals();
    }

    function calculateTotals() {
        var subtotal = 0, gstTotal = 0;
        var isGst = $('#is_gst').is(':checked');
        $('#itemsList .item-card').each(function() {
            var qty = parseFloat($(this).find('[name*="[qty]"]').val()) || 0;
            var price = parseFloat($(this).find('[name*="[unit_price]"]').val()) || 0;
            var gstPct = isGst ? (parseFloat($(this).find('[name*="[gst_percent]"]').val()) || 0) : 0;
            var disc = parseFloat($(this).find('[name*="[discount]"]').val()) || 0;
            var base = qty * price;
            var gstAmt = base * (gstPct / 100);
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
            $.each(data, function(i, site) { opts += '<option value="' + site.id + '">' + (site.site_name || site.name) + '</option>'; });
            $siteSelect.html(opts);
        }).fail(function() {
            $siteSelect.html('<option value="">-- Failed to load --</option>');
        });
    });

    $('#addItemModal').on('show.bs.modal', function() { resetModal(); });

    $('#modalProductId').on('change', function() {
        var $opt = $(this).find(':selected');
        var price = $opt.data('price') || 0;
        var warranty = $opt.data('warranty') || 12;
        var isSerialized = $opt.data('serial') == '1';
        $('#modalPrice').val(price);
        $('#modalWarranty').val(warranty);

        if (isSerialized && $(this).val()) {
            $('#modalSerialsGroup').show();
            $('#modalSerialsHint').text('Loading available serials...');
            var url = "{{ route('api.product.serials', ':id') }}".replace(':id', $(this).val());
            $.get(url, function(data) {
                if (data.length) {
                    var hint = 'Available: ' + data.map(function(s) { return s.serial_number; }).join(', ');
                    $('#modalSerialsHint').text(hint);
                } else {
                    $('#modalSerialsHint').text('No serials in stock');
                }
            });
        } else {
            $('#modalSerialsGroup').hide();
            $('#modalSerials').val('');
        }
    });

    $('#modalAddBtn').on('click', function() {
        var productId = $('#modalProductId').val();
        if (!productId) { $('#modalProductId').addClass('is-invalid').focus(); return; }
        $('#modalProductId').removeClass('is-invalid');

        var productName = $('#modalProductId option:selected').text();
        var price = parseFloat($('#modalPrice').val()) || 0;
        var qty = parseInt($('#modalQty').val()) || 1;
        if (qty < 1) qty = 1;

        addItemCard({
            product_id: productId,
            product_name: productName,
            unit_price: price,
            qty: qty,
            gst_percent: parseFloat($('#modalGst').val()) || 0,
            discount: parseFloat($('#modalDiscount').val()) || 0,
            warranty_months: parseInt($('#modalWarranty').val()) || 12,
            serial_ids: $.trim($('#modalSerials').val())
        });

        bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-card').remove();
        if ($('#itemsList .item-card').length === 0) { $('#noItemsMsg').show(); }
        calculateTotals();
    });

    $('#is_gst').on('change', function() {
        var checked = $(this).is(':checked');
        if (checked) { $('.gst-row').show(); }
        else { $('.gst-row').hide(); }
        $('#itemsList .item-card').each(function() {
            if (!checked) $(this).find('[name*="[gst_percent]"]').val(0);
        });
        calculateTotals();
    });

    $(document).on('input', '#discount', function() { calculateTotals(); });

    $('#is_gst').trigger('change');

    @if(old('customer_id'))
        $('#customer_id').trigger('change');
    @endif
});
</script>
@endsection
