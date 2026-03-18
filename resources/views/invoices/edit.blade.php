@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Edit Invoice</h4>
        <p class="text-muted mb-0">Update invoice {{ $invoice->invoice_number }}</p>
    </div>
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<form action="{{ route('invoices.update', $invoice) }}" method="POST" id="invoiceForm">
    @csrf
    @method('PUT')

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
                            <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} -- {{ $customer->phone }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="site_id" class="form-label">Site <span class="text-muted">(optional)</span></label>
                    <div class="d-flex gap-1">
                        <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id">
                            <option value="">-- Loading... --</option>
                        </select>
                        <button type="button" class="btn btn-outline-success btn-add-site flex-shrink-0" id="quickAddSiteBtn" title="Quick add site">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    @error('site_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('invoice_number') is-invalid @enderror"
                           id="invoice_number" name="invoice_number"
                           value="{{ old('invoice_number', $invoice->invoice_number) }}" required>
                    @error('invoice_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="invoice_date" class="form-label">Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
                           id="invoice_date" name="invoice_date"
                           value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" required>
                    @error('invoice_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="remaining_due_date" class="form-label">Remaining Amount Due Date</label>
                    <input type="date" class="form-control @error('remaining_due_date') is-invalid @enderror"
                           id="remaining_due_date" name="remaining_due_date"
                           value="{{ old('remaining_due_date', $invoice->remaining_due_date ? $invoice->remaining_due_date->format('Y-m-d') : '') }}">
                    @error('remaining_due_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_gst" name="is_gst" value="1"
                               {{ old('is_gst', $invoice->is_gst) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_gst">GST Invoice</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="draft" {{ old('status', $invoice->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ old('status', $invoice->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="partial" {{ old('status', $invoice->status) === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ old('status', $invoice->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
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
            <p class="text-muted text-center mb-0" id="noItemsMsg" style="display:none;">
                <i class="bi bi-inbox me-1"></i> No items added yet. Click "Add Item" to start.
            </p>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-semibold"><i class="bi bi-cash-stack me-1"></i> Expenses (internal – for profit)</span>
                <p class="small text-muted mb-0 mt-1">Internal use only; for profit calculation when no site or extra cost lines.</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="addExpenseRowBtn">
                <i class="bi bi-plus-lg me-1"></i> Add row
            </button>
        </div>
        <div class="card-body">
            <div id="expenseRowsList">
                <p class="text-muted text-center mb-0 small" id="noExpenseRowsMsg">No expense rows. Click "Add row" to add.</p>
            </div>
        </div>
    </div>

    {{-- Quick Add Site Modal --}}
    <div class="modal fade" id="quickAddSiteModal" tabindex="-1" aria-labelledby="quickAddSiteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="quickAddSiteModalLabel"><i class="bi bi-geo-alt-plus me-1"></i> Quick Add Site</h6>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2">
                    <p class="small text-muted mb-2">Selected customer ke under naya site — sirf name dalen.</p>
                    <label for="quick_site_name" class="form-label small fw-semibold">Site Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="quick_site_name" placeholder="e.g. Main Office" maxlength="255">
                    <div class="invalid-feedback" id="quick_site_name_error">Site name required.</div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-sm" id="quickAddSiteSubmit">
                        <i class="bi bi-plus-lg me-1"></i> Create & Select
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add / Edit Item Modal --}}
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
                        <select class="form-select" id="modalProductId" style="width:100%">
                            <option value="">-- Select Product --</option>
                        </select>
                        <div class="form-text text-muted"><i class="bi bi-search me-1"></i>Product name type karke search kar sakte hain</div>
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
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="modalGstToggle">
                                <label class="form-check-label fw-semibold" for="modalGstToggle">GST Apply Karen</label>
                            </div>
                        </div>
                        <div class="col-6 modal-gst-field" style="display:none;">
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
                           id="discount" name="discount" min="0" step="0.01"
                           value="{{ old('discount', $invoice->discount) }}">
                    @error('discount')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror

                    <label for="notes" class="form-label mt-3">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="3"
                              placeholder="Payment terms, delivery notes...">{{ old('notes', $invoice->notes) }}</textarea>
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
            <i class="bi bi-check-lg me-1"></i> Update Invoice
        </button>
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
</form>
@endsection

@section('scripts')
@php
    $customerSitesRoute = route('api.customer.sites', ['customer' => 1]);
    $customerSitesUrl = preg_replace('#(/api/customers/)1(/sites)#', '$1:id$2', $customerSitesRoute);
    $productSerialsRoute = route('api.product.serials', ['product' => 1]);
    $productSerialsUrl = preg_replace('#(/api/products/)1(/serials)#', '$1:id$2', $productSerialsRoute);

    $existingItems = $invoice->items->map(function($item) {
        return [
            'product_id'     => $item->product_id,
            'product_name'   => optional($item->product)->name ?? ('Product #' . $item->product_id),
            'item_type'      => optional($item->product)->type ?? 'product',
            'qty'            => (int) $item->qty,
            'unit_price'     => (float) $item->unit_price,
            'gst_percent'    => (float) $item->gst_percent,
            'discount'       => (float) $item->discount,
            'warranty_months'=> (int) $item->warranty_months,
            'serial_ids'     => $item->serialNumbers->pluck('id')->implode(','),
        ];
    })->values()->toArray();

    $existingExpenses = $invoice->invoiceExpenses->map(function($exp) {
        return [
            'description' => $exp->description,
            'amount'      => (float) $exp->amount,
        ];
    })->values()->toArray();

    $invoiceEditConfig = [
        'urls' => [
            'productsList'  => route('api.products.list'),
            'customerSites' => $customerSitesUrl,
            'customersBase' => url('customers'),
            'productSerials'=> $productSerialsUrl,
        ],
        'csrf'             => csrf_token(),
        'existingItems'    => $existingItems,
        'existingExpenses' => $existingExpenses,
        'customerId'       => $invoice->customer_id,
        'siteId'           => $invoice->site_id,
    ];
@endphp
<script type="application/json" id="invoice-edit-config">{!! json_encode($invoiceEditConfig, JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
<script>
(function() {
    var configEl = document.getElementById('invoice-edit-config');
    window.INVOICE_EDIT_CONFIG = configEl ? JSON.parse(configEl.textContent) : { urls: {}, csrf: '', existingItems: [], existingExpenses: [], customerId: '', siteId: '' };

    $(document).ready(function() {
        var rowIndex = 0;
        var expenseRowIndex = 0;
        var products = [];
        var C = window.INVOICE_EDIT_CONFIG;
        var editingRow = null;

        function escAttr(s) {
            return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function initSelect2() {
            if ($.fn.select2) {
                $('#modalProductId').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addItemModal'),
                    placeholder: '-- Product search karein --',
                    allowClear: true,
                    width: '100%',
                });
            }
        }

        function populateModalProducts() {
            var $select = $('#modalProductId');
            if ($.fn.select2 && $select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.html('<option value="">-- Loading... --</option>');
            if (!C.urls || !C.urls.productsList) {
                $select.html('<option value="">-- Select Product --</option><option value="" disabled>Config error.</option>');
                initSelect2();
                return;
            }
            $.ajax({
                url: C.urls.productsList,
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).done(function(data) {
                products = Array.isArray(data) ? data : (data && data.data ? data.data : []);
                $select.empty();
                $select.append($('<option value="">-- Product / Service search karein --</option>'));
                if (products.length > 0) {
                    var productsList = products.filter(function(p) { return (p.type || 'product') === 'product'; });
                    var servicesList = products.filter(function(p) { return p.type === 'service'; });
                    if (productsList.length > 0) {
                        var $pg = $('<optgroup label="--- Products ---"></optgroup>');
                        productsList.forEach(function(p) {
                            var opt = $('<option></option>').val(p.id).text(p.name || ('Product #' + p.id));
                            opt.attr('data-price', (p.sale_price || 0));
                            opt.attr('data-warranty', (p.warranty_months || 0));
                            opt.attr('data-serial', (p.track_serial ? '1' : '0'));
                            opt.attr('data-type', 'product');
                            $pg.append(opt);
                        });
                        $select.append($pg);
                    }
                    if (servicesList.length > 0) {
                        var $sg = $('<optgroup label="--- Services ---"></optgroup>');
                        servicesList.forEach(function(p) {
                            var opt = $('<option></option>').val(p.id).text(p.name || ('Service #' + p.id));
                            opt.attr('data-price', (p.sale_price || 0));
                            opt.attr('data-warranty', 0);
                            opt.attr('data-serial', '0');
                            opt.attr('data-type', 'service');
                            $sg.append(opt);
                        });
                        $select.append($sg);
                    }
                } else {
                    $select.append($('<option value="" disabled>No products found.</option>'));
                }
                initSelect2();
            }).fail(function(xhr) {
                var msg = 'Failed to load.';
                if (xhr.status === 403) msg = 'Access denied.';
                if (xhr.status === 500) msg = 'Server error.';
                $select.html('<option value="">-- Select Product --</option><option value="" disabled>' + msg + '</option>');
                initSelect2();
            });
        }

        function resetModal() {
            editingRow = null;
            $('#addItemModalLabel').html('<i class="bi bi-plus-circle me-1"></i> Add Item');
            $('#modalAddBtn').html('<i class="bi bi-plus-lg me-1"></i> Add to Invoice');
            if ($.fn.select2 && $('#modalProductId').hasClass('select2-hidden-accessible')) {
                $('#modalProductId').select2('destroy');
            }
            $('#modalProductId').val('').removeClass('is-invalid').prop('disabled', false);
            $('#modalPrice').val(0);
            $('#modalQty').val(1);
            $('#modalGstToggle').prop('checked', false);
            $('.modal-gst-field').hide();
            $('#modalGst').val(18);
            $('#modalDiscount').val(0);
            $('#modalWarranty').val(12);
            $('#modalSerials').val('');
            $('#modalSerialsGroup').hide();
            $('#modalSerialsHint').text('');
            populateModalProducts();
        }

        function buildItemCardHtml(idx, data) {
            var isGst  = (data.gst_percent || 0) > 0;
            var gstPct = data.gst_percent || 0;
            var base   = data.qty * data.unit_price;
            var gstAmt = base * (gstPct / 100);
            var lineTotal = base + gstAmt - (data.discount || 0);
            var displayName = data.product_name || '--';
            var isService = (data.item_type === 'service');
            var serialsDisplay = data.serial_ids ? '<span class="badge bg-info bg-opacity-10 text-info border"><i class="bi bi-upc-scan"></i> Serials</span>' : '';
            var serviceBadge = isService ? '<span class="badge bg-success-subtle text-success border"><i class="bi bi-tools me-1"></i>Service</span>' : '';

            return '<div class="item-card border rounded-3 p-3 mb-2 position-relative" data-row="' + idx + '" data-name="' + escAttr(displayName) + '">' +
                '<input type="hidden" name="items[' + idx + '][product_id]" value="' + escAttr(data.product_id || '') + '">' +
                '<input type="hidden" name="items[' + idx + '][qty]" value="' + data.qty + '">' +
                '<input type="hidden" name="items[' + idx + '][unit_price]" value="' + data.unit_price + '">' +
                '<input type="hidden" name="items[' + idx + '][gst_percent]" value="' + gstPct + '">' +
                '<input type="hidden" name="items[' + idx + '][discount]" value="' + (data.discount || 0) + '">' +
                '<input type="hidden" name="items[' + idx + '][warranty_months]" value="' + (data.warranty_months || 0) + '">' +
                '<input type="hidden" name="items[' + idx + '][serial_ids]" value="' + escAttr(data.serial_ids || '') + '">' +
                '<div class="position-absolute top-0 end-0 m-2 d-flex gap-1" style="z-index:1;">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary edit-item" title="Edit"><i class="bi bi-pencil"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove"><i class="bi bi-x-lg"></i></button>' +
                '</div>' +
                '<div class="fw-semibold mb-1" style="padding-right:5rem;">' + $('<span>').text(displayName).html() + '</div>' +
                '<div class="d-flex flex-wrap gap-2 small">' +
                    serviceBadge +
                    '<span class="badge bg-light text-dark border">Qty: ' + data.qty + '</span>' +
                    '<span class="badge bg-light text-dark border">₹' + parseFloat(data.unit_price).toFixed(2) + '</span>' +
                    (isGst ? '<span class="badge bg-warning bg-opacity-25 text-dark border">' + gstPct + '% GST</span>' : '<span class="badge bg-light text-muted border">No GST</span>') +
                    (data.discount > 0 ? '<span class="badge bg-danger bg-opacity-10 text-danger border">-₹' + parseFloat(data.discount).toFixed(2) + '</span>' : '') +
                    (!isService ? '<span class="badge bg-light text-dark border"><i class="bi bi-shield-check"></i> ' + (data.warranty_months || 0) + ' Mo</span>' : '') +
                    serialsDisplay +
                '</div>' +
                '<div class="text-end fw-bold ' + (isService ? 'text-warning' : 'text-success') + ' mt-1">₹' + lineTotal.toFixed(2) + '</div>' +
            '</div>';
        }

        function addItemCard(data) {
            $('#itemsList').append(buildItemCardHtml(rowIndex, data));
            $('#noItemsMsg').hide();
            rowIndex++;
            calculateTotals();
        }

        function calculateTotals() {
            var subtotal = 0, gstTotal = 0;
            var isGst = $('#is_gst').is(':checked');
            $('#itemsList .item-card').each(function() {
                var qty   = parseFloat($(this).find('[name*="[qty]"]').val()) || 0;
                var price = parseFloat($(this).find('[name*="[unit_price]"]').val()) || 0;
                var gPct  = isGst ? (parseFloat($(this).find('[name*="[gst_percent]"]').val()) || 0) : 0;
                var disc  = parseFloat($(this).find('[name*="[discount]"]').val()) || 0;
                var base  = qty * price;
                subtotal += base;
                gstTotal += base * (gPct / 100);
            });
            var overallDisc  = parseFloat($('#discount').val()) || 0;
            var grandTotal   = subtotal + gstTotal - overallDisc;
            $('#summarySubtotal').text('₹' + subtotal.toFixed(2));
            $('#summaryGst').text('₹' + gstTotal.toFixed(2));
            $('#summaryDiscount').text('-₹' + overallDisc.toFixed(2));
            $('#summaryGrandTotal').text('₹' + grandTotal.toFixed(2));
        }

        function loadCustomerSites(customerId, selectedSiteId) {
            var $siteSelect = $('#site_id');
            if (!customerId) { $siteSelect.html('<option value="">-- Select Customer First --</option>'); return; }
            $siteSelect.html('<option value="">-- Loading... --</option>');
            var url = C.urls.customerSites.replace(':id', customerId);
            $.get(url, function(data) {
                var opts = '<option value="">-- None --</option>';
                $.each(data, function(i, site) {
                    var sel = (selectedSiteId && site.id == selectedSiteId) ? ' selected' : '';
                    opts += '<option value="' + site.id + '"' + sel + '>' + escAttr(site.site_name || site.name) + '</option>';
                });
                $siteSelect.html(opts);
            }).fail(function() {
                $siteSelect.html('<option value="">-- Failed to load --</option>');
            });
        }

        function addExpenseRow(desc, amt) {
            var idx = expenseRowIndex++;
            var html = '<div class="row g-2 mb-2 expense-row align-items-end" data-expense-index="' + idx + '">' +
                '<div class="col-md-7"><label class="form-label small">Description</label>' +
                '<input type="text" class="form-control form-control-sm" name="expenses[' + idx + '][description]" placeholder="e.g. Labour, material" value="' + escAttr(desc || '') + '">' +
                '</div><div class="col-md-3"><label class="form-label small">Amount (₹)</label>' +
                '<input type="number" class="form-control form-control-sm" name="expenses[' + idx + '][amount]" min="0" step="0.01" value="' + parseFloat(amt || 0).toFixed(2) + '">' +
                '</div><div class="col-md-2"><label class="form-label small d-none d-md-block">&nbsp;</label>' +
                '<button type="button" class="btn btn-sm btn-outline-danger w-100 remove-expense-row"><i class="bi bi-dash-lg"></i></button></div></div>';
            $('#expenseRowsList').append(html);
            $('#noExpenseRowsMsg').hide();
        }

        function reindexExpenseRows() {
            $('#expenseRowsList .expense-row').each(function(i) {
                $(this).attr('data-expense-index', i);
                $(this).find('input[name*="[description]"]').attr('name', 'expenses[' + i + '][description]');
                $(this).find('input[name*="[amount]"]').attr('name', 'expenses[' + i + '][amount]');
            });
            expenseRowIndex = $('#expenseRowsList .expense-row').length;
            if (expenseRowIndex === 0) { $('#noExpenseRowsMsg').show(); }
        }

        // ---- Initialize ----
        loadCustomerSites(C.customerId, C.siteId);

        if (C.existingItems && C.existingItems.length > 0) {
            C.existingItems.forEach(function(item) { addItemCard(item); });
        } else {
            $('#noItemsMsg').show();
        }

        if (C.existingExpenses && C.existingExpenses.length > 0) {
            C.existingExpenses.forEach(function(exp) { addExpenseRow(exp.description, exp.amount); });
        }

        // ---- GST toggle ----
        $('#is_gst').on('change', function() {
            if ($(this).is(':checked')) { $('.gst-row').show(); }
            else { $('.gst-row').hide(); $('#itemsList .item-card').each(function() { $(this).find('[name*="[gst_percent]"]').val(0); }); }
            calculateTotals();
        });
        $('#is_gst').trigger('change');

        // ---- Customer change ----
        $('#customer_id').on('change', function() { loadCustomerSites($(this).val(), null); });

        // ---- Quick add site ----
        $('#quickAddSiteBtn').on('click', function() {
            if (!$('#customer_id').val()) { alert('Pehle Customer select karein.'); $('#customer_id').focus(); return; }
            $('#quick_site_name').val('').removeClass('is-invalid');
            $('#quick_site_name_error').text('Site name required.');
            new bootstrap.Modal(document.getElementById('quickAddSiteModal')).show();
            setTimeout(function() { $('#quick_site_name').focus(); }, 300);
        });
        $('#quickAddSiteSubmit').on('click', function() { submitQuickSite(); });
        $('#quick_site_name').on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); submitQuickSite(); } });
        function submitQuickSite() {
            var siteName = $.trim($('#quick_site_name').val());
            if (!siteName) { $('#quick_site_name').addClass('is-invalid').focus(); return; }
            $('#quick_site_name').removeClass('is-invalid');
            var customerId = $('#customer_id').val();
            var $btn = $('#quickAddSiteSubmit');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');
            $.ajax({
                url: C.urls.customersBase + '/' + customerId + '/sites',
                method: 'POST',
                data: { _token: C.csrf, site_name: siteName, address: '', contact_person: '', contact_phone: '', notes: '' },
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).done(function(res) {
                var url = C.urls.customerSites.replace(':id', customerId);
                $.get(url, function(data) {
                    var opts = '<option value="">-- Select Site --</option>';
                    $.each(data, function(i, site) {
                        opts += '<option value="' + site.id + '"' + (site.id == res.id ? ' selected' : '') + '>' + escAttr(site.site_name || site.name) + '</option>';
                    });
                    $('#site_id').html(opts);
                });
                bootstrap.Modal.getInstance(document.getElementById('quickAddSiteModal')).hide();
            }).fail(function(xhr) {
                var msg = 'Site create nahi ho paya.';
                if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.site_name) { msg = xhr.responseJSON.errors.site_name[0]; }
                else if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                $('#quick_site_name_error').text(msg);
                $('#quick_site_name').addClass('is-invalid');
            }).always(function() {
                $btn.prop('disabled', false).html('<i class="bi bi-plus-lg me-1"></i> Create & Select');
            });
        }

        // ---- Item modal ----
        $('#addItemModal').on('show.bs.modal', function() { resetModal(); });
        populateModalProducts();

        $('#modalProductId').on('change', function() {
            var $opt = $(this).find(':selected');
            var itemType = $opt.data('type') || 'product';
            $('#modalPrice').val($opt.data('price') || 0);
            $('#modalWarranty').val($opt.data('warranty') || 12);
            var isSerialized = $opt.data('serial') == '1';
            if (itemType === 'service') {
                $('#modalSerialsGroup').hide();
                $('#modalSerials').val('');
                $('[for="modalWarranty"]').closest('.col-6').hide();
            } else {
                $('[for="modalWarranty"]').closest('.col-6').show();
                if (isSerialized && $(this).val()) {
                    $('#modalSerialsGroup').show();
                    $('#modalSerialsHint').text('Loading available serials...');
                    $.get(C.urls.productSerials.replace(':id', $(this).val()), function(data) {
                        $('#modalSerialsHint').text(data.length ? 'Available: ' + data.map(function(s) { return s.serial_number; }).join(', ') : 'No serials in stock');
                    });
                } else {
                    $('#modalSerialsGroup').hide();
                    $('#modalSerials').val('');
                }
            }
        });

        $('#modalGstToggle').on('change', function() {
            if ($(this).is(':checked')) { $('.modal-gst-field').show(); } else { $('.modal-gst-field').hide(); }
        });

        $('#modalAddBtn').on('click', function() {
            var productId = $('#modalProductId').val();
            if (!productId) { $('#modalProductId').addClass('is-invalid').focus(); return; }
            $('#modalProductId').removeClass('is-invalid');
            var qty = parseInt($('#modalQty').val()) || 1;
            if (qty < 1) qty = 1;
            var $selectedOpt = $('#modalProductId option:selected');
            var itemType = $selectedOpt.data('type') || 'product';
            var data = {
                product_id:      productId,
                product_name:    $selectedOpt.text(),
                item_type:       itemType,
                unit_price:      parseFloat($('#modalPrice').val()) || 0,
                qty:             qty,
                gst_percent:     $('#modalGstToggle').is(':checked') ? (parseFloat($('#modalGst').val()) || 0) : 0,
                discount:        parseFloat($('#modalDiscount').val()) || 0,
                warranty_months: itemType === 'service' ? 0 : (parseInt($('#modalWarranty').val()) || 12),
                serial_ids:      itemType === 'service' ? '' : $.trim($('#modalSerials').val())
            };
            if (editingRow !== null) {
                $('#itemsList .item-card[data-row="' + editingRow + '"]').replaceWith(buildItemCardHtml(editingRow, data));
                calculateTotals();
            } else {
                addItemCard(data);
            }
            bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
        });

        $(document).on('click', '.edit-item', function() {
            var $card = $(this).closest('.item-card');
            var idx = $card.data('row');
            editingRow = idx;
            var productId   = $card.find('[name*="[product_id]"]').val();
            var qty         = $card.find('[name*="[qty]"]').val();
            var price       = $card.find('[name*="[unit_price]"]').val();
            var gstPct      = parseFloat($card.find('[name*="[gst_percent]"]').val()) || 0;
            var discount    = $card.find('[name*="[discount]"]').val();
            var warranty    = $card.find('[name*="[warranty_months]"]').val();
            var serials     = $card.find('[name*="[serial_ids]"]').val();
            var productName = $card.data('name') || '';
            resetModal();
            editingRow = idx;
            $('#addItemModalLabel').html('<i class="bi bi-pencil me-1"></i> Edit Item');
            $('#modalAddBtn').html('<i class="bi bi-check-lg me-1"></i> Update Item');
            $('#modalPrice').val(price);
            $('#modalQty').val(qty);
            $('#modalDiscount').val(discount);
            $('#modalWarranty').val(warranty);
            $('#modalSerials').val(serials);
            if (gstPct > 0) { $('#modalGstToggle').prop('checked', true); $('.modal-gst-field').show(); $('#modalGst').val(gstPct); }
            var checkReady = setInterval(function() {
                if (!$('#modalProductId').hasClass('select2-hidden-accessible')) return;
                clearInterval(checkReady);
                if (productId) {
                    if ($('#modalProductId option[value="' + productId + '"]').length === 0) {
                        $('#modalProductId').append('<option value="' + productId + '">' + escAttr(productName) + '</option>');
                    }
                    $('#modalProductId').val(productId).trigger('change.select2');
                }
            }, 100);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addItemModal')).show();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('.item-card').remove();
            if ($('#itemsList .item-card').length === 0) { $('#noItemsMsg').show(); }
            calculateTotals();
        });

        $(document).on('input', '#discount', function() { calculateTotals(); });

        $('#addExpenseRowBtn').on('click', function() { addExpenseRow('', 0); });

        $(document).on('click', '.remove-expense-row', function() {
            $(this).closest('.expense-row').remove();
            reindexExpenseRows();
        });
    });
})();
</script>
@endsection
