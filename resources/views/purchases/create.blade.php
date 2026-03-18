@extends('layouts.app')

@section('title', 'New Purchase')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">New Purchase</h4>
        <p class="text-muted mb-0">Record a purchase bill from a vendor</p>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-info-circle me-1"></i> Bill Details
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id" required>
                        <option value="">— Select Vendor —</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="bill_number" class="form-label">Bill Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('bill_number') is-invalid @enderror"
                           id="bill_number" name="bill_number" value="{{ old('bill_number') }}" required>
                    @error('bill_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="bill_date" class="form-label">Bill Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('bill_date') is-invalid @enderror"
                           id="bill_date" name="bill_date" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                    @error('bill_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-list-ul me-1"></i> Items</span>
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

    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel"><i class="bi bi-plus-circle me-1"></i> Add Purchase Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filter by Category</label>
                        <select class="form-select" id="modalCategoryFilter">
                            <option value="">-- All Categories --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <select class="form-select" id="modalProductId" style="width:100%;">
                                    <option value="">-- Select Product --</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-outline-success flex-shrink-0" id="quickCreateProductBtn" title="Create New Product">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                        <select class="form-select" id="modalVendorId">
                            <option value="">-- Select Vendor --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Purchase Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="modalGstType">
                            <option value="with_gst" selected>With GST</option>
                            <option value="without_gst">Without GST</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Qty <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modalQty" min="1" value="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Purchase Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="modalPrice" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Sale Price (₹)</label>
                            <input type="number" class="form-control" id="modalSalePrice" min="0" step="0.01" value="0"
                                   placeholder="Product ka selling price">
                            <div class="form-text text-success small">Save hone par product ka sale price update ho jayega</div>
                        </div>
                        <div class="col-6" id="modalGstWrapper">
                            <label class="form-label fw-semibold">GST %</label>
                            <input type="number" class="form-control" id="modalGst" min="0" max="100" step="0.01" value="18">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Serial Numbers</label>
                            <input type="text" class="form-control" id="modalSerials" placeholder="SN1, SN2, SN3">
                            <div class="form-text">Comma se separate karein. Example: CP001, CP002</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="modalAddBtn">
                        <i class="bi bi-plus-lg me-1"></i> Add to Purchase
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quickProductModal" tabindex="-1" aria-labelledby="quickProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickProductModalLabel"><i class="bi bi-box-seam me-1"></i> Quick Create Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="qpName" placeholder="e.g. CP Plus 2MP Dome Camera">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="qpCategory">
                                <option value="">-- Select --</option>
                                <option value="Camera">Camera</option>
                                <option value="DVR_NVR">DVR/NVR</option>
                                <option value="HDD">HDD</option>
                                <option value="Cable">Cable</option>
                                <option value="SMPS">SMPS</option>
                                <option value="Accessories">Accessories</option>
                                <option value="IP">IP</option>
                                <option value="Analog">Analog</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Brand</label>
                            <input type="text" class="form-control" id="qpBrand" placeholder="e.g. CP Plus">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Sale Price (₹)</label>
                            <input type="number" class="form-control" id="qpSalePrice" min="0" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Warranty (months)</label>
                            <input type="number" class="form-control" id="qpWarranty" min="0" placeholder="e.g. 12">
                        </div>
                    </div>
                    <div id="qpError" class="alert alert-danger small mt-3 d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="qpSaveBtn">
                        <i class="bi bi-check-lg me-1"></i> Create & Select
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="bi bi-calculator me-1"></i> Summary</div>
        <div class="card-body">
            <table class="table table-borderless table-sm mb-0">
                <tr><td class="text-muted">Grand Total</td><td class="text-end fw-bold text-success" id="grandTotal">₹0.00</td></tr>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror"
                      id="notes" name="notes" rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
            @error('notes')
                <span class="text-danger small">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> Save Purchase
        </button>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection

@section('scripts')
@php
    $purchaseProducts = $products->map(function ($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'category' => $p->category,
            'purchase_price' => $p->purchaseItems()->avg('unit_price'),
            'sale_price' => $p->sale_price ? (float) $p->sale_price : null,
        ];
    })->values();
    $productNameMap = $products->pluck('name', 'id');
    $oldPurchaseItems = collect(old('items', []))->map(function ($item) use ($productNameMap) {
        return [
            'product_id' => (string) ($item['product_id'] ?? ''),
            'product_name' => $productNameMap[(int) ($item['product_id'] ?? 0)] ?? 'Product',
            'qty' => (int) ($item['qty'] ?? 1),
            'unit_price' => (float) ($item['unit_price'] ?? 0),
            'sale_price' => (float) ($item['sale_price'] ?? 0),
            'gst_percent' => (float) ($item['gst_percent'] ?? 0),
            'serials' => (string) ($item['serials'] ?? ''),
        ];
    })->values();
@endphp
<script id="purchaseProductsJson" type="application/json">@json($purchaseProducts)</script>
<script id="oldPurchaseItemsJson" type="application/json">@json($oldPurchaseItems)</script>
<script>
$(document).ready(function() {
    var rowIndex = 0;

    var products = JSON.parse(document.getElementById('purchaseProductsJson').textContent || '[]');
    var oldPurchaseItems = JSON.parse(document.getElementById('oldPurchaseItemsJson').textContent || '[]');

    function populateCategoryFilter() {
        var cats = {};
        products.forEach(function(p) { if (p.category) cats[p.category] = true; });
        var opts = '<option value="">-- All Categories --</option>';
        Object.keys(cats).sort().forEach(function(c) {
            opts += '<option value="' + c + '">' + c.replace('_', '/') + '</option>';
        });
        $('#modalCategoryFilter').html(opts);
    }

    function populateModalProducts(selectedCategory) {
        var opts = '<option value="">-- Select Product --</option>';
        products.forEach(function(p) {
            if (selectedCategory && p.category !== selectedCategory) return;
            opts += '<option value="' + p.id + '" data-price="' + (p.purchase_price || 0) + '" data-sale-price="' + (p.sale_price || 0) + '">' + p.name + '</option>';
        });
        $('#modalProductId').html(opts).trigger('change.select2');
    }

    function resetModal() {
        $('#modalCategoryFilter').val('');
        $('#modalProductId').val(null).trigger('change.select2');
        $('#modalQty').val(1);
        $('#modalPrice').val(0);
        $('#modalSalePrice').val(0);
        $('#modalGstType').val('with_gst');
        $('#modalGst').val(18).prop('disabled', false);
        $('#modalGstWrapper').show();
        $('#modalSerials').val('');
        populateCategoryFilter();
        populateModalProducts();
        var currentVendor = $('#vendor_id').val();
        $('#modalVendorId').val(currentVendor || '');
    }

    function addItemCard(data) {
        var lineBase = data.qty * data.unit_price;
        var lineGst = lineBase * ((data.gst_percent || 0) / 100);
        var lineTotal = lineBase + lineGst;
        var serialText = data.serials ? data.serials : '—';
        var salePrice = parseFloat(data.sale_price || 0);

        var html = '<div class="item-card border rounded-3 p-3 mb-2 position-relative" data-row="' + rowIndex + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][product_id]" value="' + data.product_id + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][qty]" value="' + data.qty + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][unit_price]" value="' + data.unit_price + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][sale_price]" value="' + salePrice + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][gst_percent]" value="' + (data.gst_percent || 0) + '">' +
            '<input type="hidden" name="items[' + rowIndex + '][serials]" value="' + $('<span>').text(data.serials || '').html() + '">' +
            '<button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 remove-item" style="z-index:1;"><i class="bi bi-x-lg"></i></button>' +
            '<div class="fw-semibold mb-1" style="padding-right:2rem;">' + $('<span>').text(data.product_name).html() + '</div>' +
            '<div class="d-flex flex-wrap gap-2 small mb-2">' +
                '<span class="badge bg-light text-dark border">Qty: ' + data.qty + '</span>' +
                '<span class="badge bg-light text-dark border">Buy: ₹' + parseFloat(data.unit_price).toFixed(2) + '</span>' +
                (salePrice > 0 ? '<span class="badge bg-info-subtle text-info border">Sale: ₹' + salePrice.toFixed(2) + '</span>' : '') +
                (data.gst_percent > 0
                    ? '<span class="badge bg-success-subtle text-success border">With GST (' + data.gst_percent + '%)</span>'
                    : '<span class="badge bg-warning-subtle text-warning border">Without GST</span>') +
            '</div>' +
            '<div class="small text-muted mb-1">Serials: ' + $('<span>').text(serialText).html() + '</div>' +
            '<div class="text-end fw-bold text-success">₹' + lineTotal.toFixed(2) + '</div>' +
        '</div>';

        $('#itemsList').append(html);
        $('#noItemsMsg').hide();
        rowIndex++;
        calculateTotals();
    }

    function calculateTotals() {
        var grandTotal = 0;
        $('#itemsList .item-card').each(function() {
            var qty = parseFloat($(this).find('[name*="[qty]"]').val()) || 0;
            var price = parseFloat($(this).find('[name*="[unit_price]"]').val()) || 0;
            var gst = parseFloat($(this).find('[name*="[gst_percent]"]').val()) || 0;
            var lineTotal = qty * price * (1 + gst / 100);
            grandTotal += lineTotal;
        });
        $('#grandTotal').text('₹' + grandTotal.toFixed(2));
    }

    $('#modalProductId').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addItemModal'),
        placeholder: '-- Select Product --',
        allowClear: true
    });

    $('#addItemModal').on('show.bs.modal', function() {
        resetModal();
    });

    $('#modalCategoryFilter').on('change', function() {
        populateModalProducts($(this).val());
    });

    $('#modalProductId').on('change', function() {
        var $opt = $(this).find(':selected');
        $('#modalPrice').val($opt.data('price') || 0);
        $('#modalSalePrice').val($opt.data('sale-price') || 0);
    });

    $('#modalGstType').on('change', function() {
        if ($(this).val() === 'without_gst') {
            $('#modalGst').val(0).prop('disabled', true);
            $('#modalGstWrapper').hide();
        } else {
            $('#modalGst').val(18).prop('disabled', false);
            $('#modalGstWrapper').show();
        }
    });

    $('#modalVendorId').on('change', function() {
        $('#vendor_id').val($(this).val());
        $(this).removeClass('is-invalid');
    });

    $('#modalAddBtn').on('click', function() {
        var productId = $('#modalProductId').val();
        var vendorId = $('#modalVendorId').val();
        var hasError = false;

        if (!productId) {
            $('#modalProductId').addClass('is-invalid').focus();
            hasError = true;
        } else {
            $('#modalProductId').removeClass('is-invalid');
        }

        if (!vendorId) {
            $('#modalVendorId').addClass('is-invalid');
            if (!hasError) $('#modalVendorId').focus();
            hasError = true;
        } else {
            $('#modalVendorId').removeClass('is-invalid');
        }

        if (hasError) return;

        $('#vendor_id').val(vendorId);

        var productName = $('#modalProductId option:selected').text();
        var qty = parseInt($('#modalQty').val(), 10) || 1;
        var unitPrice = parseFloat($('#modalPrice').val()) || 0;
        var salePrice = parseFloat($('#modalSalePrice').val()) || 0;
        var gstPercent = ($('#modalGstType').val() === 'without_gst') ? 0 : (parseFloat($('#modalGst').val()) || 0);
        var serials = $.trim($('#modalSerials').val());
        if (qty < 1) qty = 1;

        addItemCard({
            product_id: productId,
            product_name: productName,
            qty: qty,
            unit_price: unitPrice,
            sale_price: salePrice,
            gst_percent: gstPercent,
            serials: serials
        });

        bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-card').remove();
        if ($('#itemsList .item-card').length === 0) {
            $('#noItemsMsg').show();
        }
        calculateTotals();
    });

    $('#purchaseForm').on('submit', function(e) {
        if ($('#itemsList .item-card').length === 0) {
            e.preventDefault();
            alert('Please add at least one item.');
        }
    });

    $('#quickCreateProductBtn').on('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
        $('#qpName').val('');
        $('#qpCategory').val('');
        $('#qpBrand').val('');
        $('#qpSalePrice').val('');
        $('#qpWarranty').val('');
        $('#qpError').addClass('d-none');
        $('#qpName, #qpCategory').removeClass('is-invalid');
        new bootstrap.Modal('#quickProductModal').show();
    });

    $('#quickProductModal').on('hidden.bs.modal', function() {
        new bootstrap.Modal('#addItemModal').show();
    });

    $('#qpSaveBtn').on('click', function() {
        var name = $.trim($('#qpName').val());
        var category = $('#qpCategory').val();
        var hasErr = false;
        $('#qpName, #qpCategory').removeClass('is-invalid');
        $('#qpError').addClass('d-none');

        if (!name) { $('#qpName').addClass('is-invalid'); hasErr = true; }
        if (!category) { $('#qpCategory').addClass('is-invalid'); hasErr = true; }
        if (hasErr) return;

        var btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Creating...');

        $.ajax({
            url: '/api/products/quick-store',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                name: name,
                category: category,
                brand: $.trim($('#qpBrand').val()) || null,
                sale_price: $('#qpSalePrice').val() || null,
                warranty_months: $('#qpWarranty').val() || null,
            },
            success: function(data) {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Create & Select');
                if (data.success) {
                    products.push(data.product);
                    populateCategoryFilter();
                    populateModalProducts($('#modalCategoryFilter').val());
                    $('#modalProductId').val(data.product.id).trigger('change.select2');
                    bootstrap.Modal.getInstance(document.getElementById('quickProductModal')).hide();
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Create & Select');
                var msg = 'Failed to create product.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errs = xhr.responseJSON.errors;
                    msg = Object.values(errs).flat().join('<br>');
                }
                $('#qpError').removeClass('d-none').html(msg);
            }
        });
    });

    oldPurchaseItems.forEach(function(oldItem) {
        addItemCard({
            product_id: oldItem.product_id,
            product_name: oldItem.product_name,
            qty: oldItem.qty,
            unit_price: oldItem.unit_price,
            sale_price: oldItem.sale_price,
            gst_percent: oldItem.gst_percent,
            serials: oldItem.serials
        });
    });
});
</script>
@endsection
