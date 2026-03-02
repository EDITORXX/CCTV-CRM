@extends('layouts.app')

@section('title', 'New Estimate')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">New Estimate</h4>
        <p class="text-muted mb-0">
            @if(isset($template))
                Prefilled from template: <strong>{{ $template->name }}</strong> — select customer/site and submit.
            @else
                Create an estimate for a customer (no stock check)
            @endif
        </p>
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
                <div class="col-12">
                    <label class="form-label fw-semibold">Customer Type <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_type" id="customerTypeExisting" value="existing"
                                   {{ old('customer_type', 'existing') === 'existing' ? 'checked' : '' }}>
                            <label class="form-check-label" for="customerTypeExisting">
                                <i class="bi bi-person-check me-1"></i> Registered Customer
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="customer_type" id="customerTypeWalkin" value="walkin"
                                   {{ old('customer_type') === 'walkin' ? 'checked' : '' }}>
                            <label class="form-check-label" for="customerTypeWalkin">
                                <i class="bi bi-person-plus me-1"></i> Walk-in (Naam se banaao)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Existing customer fields --}}
                <div class="col-md-4" id="existingCustomerFields">
                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                        <option value="">-- Select Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} -- {{ $customer->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Walk-in customer fields --}}
                <div class="col-md-4" id="walkinCustomerFields" style="display:none;">
                    <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror"
                           id="customer_name" name="customer_name" value="{{ old('customer_name') }}" placeholder="Customer ka naam likhein">
                </div>
                <div class="col-md-4" id="walkinPhoneField" style="display:none;">
                    <label for="customer_phone" class="form-label">Phone (Optional)</label>
                    <input type="text" class="form-control" id="customer_phone" name="customer_phone"
                           value="{{ old('customer_phone') }}" placeholder="Phone number">
                </div>

                <div class="col-md-4" id="siteField">
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
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-primary flex-fill" id="modalModeSelect">
                            <i class="bi bi-list-ul me-1"></i> Select Product
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="modalModeCustom">
                            <i class="bi bi-pencil me-1"></i> Custom Product
                        </button>
                    </div>

                    <div id="modalSelectGroup" class="mb-3">
                        <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="modalProductId">
                            <option value="">-- Select Product --</option>
                        </select>
                    </div>

                    <div id="modalCustomGroup" class="mb-3" style="display:none;">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modalDescription" placeholder="e.g. CP Plus 2 Mp Camera">
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

                    <div id="modalSaveAsProductGroup" class="mt-3" style="display:none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modalSaveAsProduct">
                            <label class="form-check-label" for="modalSaveAsProduct">
                                <i class="bi bi-floppy me-1"></i> Save as Product (product list mein add ho jayega)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="modalAddBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add to Estimate
                    </button>
                </div>
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
    var isCustomMode = false;
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

    function populateModalProducts() {
        var opts = '<option value="">-- Select Product --</option>';
        products.forEach(function(p) {
            opts += '<option value="' + p.id + '" data-price="' + (p.sale_price || 0) + '" data-warranty="' + (p.warranty_months || 0) + '">' + p.name + '</option>';
        });
        $('#modalProductId').html(opts);
    }

    function resetModal() {
        isCustomMode = false;
        $('#modalModeSelect').removeClass('btn-outline-secondary').addClass('btn-primary');
        $('#modalModeCustom').removeClass('btn-primary').addClass('btn-outline-secondary');
        $('#modalSelectGroup').show();
        $('#modalCustomGroup').hide();
        $('#modalSaveAsProductGroup').hide();
        $('#modalProductId').val('');
        $('#modalDescription').val('');
        $('#modalPrice').val(0);
        $('#modalQty').val(1);
        $('#modalGst').val(18);
        $('#modalDiscount').val(0);
        $('#modalWarranty').val(12);
        $('#modalSaveAsProduct').prop('checked', false);
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
        var displayName = data.product_name || data.description || '—';
        var gstInfo = isGst ? '<span class="text-muted">GST: ' + gstPct + '%</span>' : '';
        var discInfo = data.discount > 0 ? '<span class="text-danger">Disc: ₹' + parseFloat(data.discount).toFixed(0) + '</span>' : '';

        var html = '<div class="item-card border rounded-3 p-3 mb-2 position-relative" data-row="' + rowIndex + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][product_id]" value="' + (data.product_id || '') + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][description]" value="' + (data.description || '').replace(/"/g, '&quot;') + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][qty]" value="' + data.qty + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][unit_price]" value="' + data.unit_price + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][gst_percent]" value="' + gstPct + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][discount]" value="' + (data.discount || 0) + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][warranty_months]" value="' + (data.warranty_months || 0) + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][save_as_product]" value="' + (data.save_as_product ? 1 : 0) + '">' +
            '<button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 remove-item" style="z-index:1;"><i class="bi bi-x-lg"></i></button>' +
            '<div class="fw-semibold mb-1" style="padding-right:2rem;">' + $('<span>').text(displayName).html() + '</div>' +
            '<div class="d-flex flex-wrap gap-2 small">' +
                '<span class="badge bg-light text-dark border">Qty: ' + data.qty + '</span>' +
                '<span class="badge bg-light text-dark border">₹' + parseFloat(data.unit_price).toFixed(0) + '</span>' +
                (gstInfo ? '<span class="badge bg-light text-dark border">' + gstPct + '% GST</span>' : '') +
                (data.discount > 0 ? '<span class="badge bg-danger bg-opacity-10 text-danger border">-₹' + parseFloat(data.discount).toFixed(0) + '</span>' : '') +
                '<span class="badge bg-light text-dark border"><i class="bi bi-shield-check"></i> ' + (data.warranty_months || 0) + ' Mo</span>' +
                (data.save_as_product ? '<span class="badge bg-success bg-opacity-10 text-success border"><i class="bi bi-floppy"></i> Save</span>' : '') +
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

    function toggleCustomerType() {
        var isWalkin = $('#customerTypeWalkin').is(':checked');
        if (isWalkin) {
            $('#existingCustomerFields').hide();
            $('#walkinCustomerFields').show();
            $('#walkinPhoneField').show();
            $('#siteField').hide();
            $('#customer_id').prop('required', false).val('');
            $('#customer_name').prop('required', true);
            $('#site_id').val('');
        } else {
            $('#existingCustomerFields').show();
            $('#walkinCustomerFields').hide();
            $('#walkinPhoneField').hide();
            $('#siteField').show();
            $('#customer_id').prop('required', true);
            $('#customer_name').prop('required', false).val('');
            $('#customer_phone').val('');
        }
    }

    $('input[name="customer_type"]').on('change', toggleCustomerType);
    toggleCustomerType();

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

    $('#addItemModal').on('show.bs.modal', function() { resetModal(); });

    $('#modalModeSelect').on('click', function() {
        isCustomMode = false;
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        $('#modalModeCustom').removeClass('btn-primary').addClass('btn-outline-secondary');
        $('#modalSelectGroup').show();
        $('#modalCustomGroup').hide();
        $('#modalSaveAsProductGroup').hide();
        $('#modalDescription').val('');
        $('#modalSaveAsProduct').prop('checked', false);
    });

    $('#modalModeCustom').on('click', function() {
        isCustomMode = true;
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
        $('#modalModeSelect').removeClass('btn-primary').addClass('btn-outline-secondary');
        $('#modalSelectGroup').hide();
        $('#modalCustomGroup').show();
        $('#modalSaveAsProductGroup').show();
        $('#modalProductId').val('');
        $('#modalPrice').val(0);
        $('#modalWarranty').val(12);
        $('#modalDescription').focus();
    });

    $('#modalProductId').on('change', function() {
        var $opt = $(this).find(':selected');
        $('#modalPrice').val($opt.data('price') || 0);
        $('#modalWarranty').val($opt.data('warranty') || 12);
    });

    $('#modalAddBtn').on('click', function() {
        var productId = '', productName = '', description = '', saveAsProduct = false;
        if (isCustomMode) {
            description = $.trim($('#modalDescription').val());
            if (!description) { $('#modalDescription').addClass('is-invalid').focus(); return; }
            $('#modalDescription').removeClass('is-invalid');
            productName = description;
            saveAsProduct = $('#modalSaveAsProduct').is(':checked');
        } else {
            productId = $('#modalProductId').val();
            if (!productId) { $('#modalProductId').addClass('is-invalid').focus(); return; }
            $('#modalProductId').removeClass('is-invalid');
            productName = $('#modalProductId option:selected').text();
        }

        var price = parseFloat($('#modalPrice').val()) || 0;
        var qty = parseInt($('#modalQty').val()) || 1;
        if (qty < 1) qty = 1;

        addItemCard({
            product_id: productId,
            product_name: productName,
            description: description,
            unit_price: price,
            qty: qty,
            gst_percent: parseFloat($('#modalGst').val()) || 0,
            discount: parseFloat($('#modalDiscount').val()) || 0,
            warranty_months: parseInt($('#modalWarranty').val()) || 12,
            save_as_product: saveAsProduct
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

    @if(isset($template) && $template->items->isNotEmpty())
        @foreach($template->items as $ti)
        addItemCard({
            product_id: {!! $ti->product_id ? $ti->product_id : "''" !!},
            product_name: {!! json_encode($ti->product ? $ti->product->name : ($ti->description ?? '')) !!},
            description: {!! json_encode($ti->description ?? '') !!},
            unit_price: {{ $ti->unit_price }},
            qty: {{ $ti->qty }},
            gst_percent: {{ $ti->gst_percent ?? 18 }},
            discount: {{ $ti->discount ?? 0 }},
            warranty_months: {{ $ti->warranty_months ?? 12 }},
            save_as_product: false
        });
        @endforeach
    @endif
});
</script>
@endsection
