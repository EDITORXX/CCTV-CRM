@extends('layouts.app')

@section('title', 'New Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">New Invoice</h4>
        <p class="text-muted mb-0">Create a sales invoice for a customer</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
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
                        <option value="">— Select Customer —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} — {{ $customer->phone }}
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
                        <option value="">— Select Customer First —</option>
                    </select>
                    @error('site_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('invoice_number') is-invalid @enderror"
                           id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $nextInvoiceNumber ?? '') }}" required>
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
            <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="200">Product</th>
                            <th width="70">Qty</th>
                            <th width="110">Unit Price</th>
                            <th width="80" class="gst-col">GST%</th>
                            <th width="100">Discount</th>
                            <th width="90">Warranty (Mo)</th>
                            <th>Serial Numbers</th>
                            <th width="120">Line Total</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                </table>
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

    var products = @json($products->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'category' => $p->category->name ?? '',
            'is_serialized' => $p->is_serialized ?? false,
        ];
    }));

    function buildProductOptions() {
        var opts = '<option value="">— Select Product —</option>';
        products.forEach(function(p) {
            var label = p.name + (p.category ? ' [' + p.category + ']' : '');
            opts += '<option value="' + p.id + '" data-serialized="' + (p.is_serialized ? '1' : '0') + '">' + label + '</option>';
        });
        return opts;
    }

    function addItemRow() {
        var gstDisplay = $('#is_gst').is(':checked') ? '' : 'style="display:none"';
        var html = '<tr data-row="' + rowIndex + '">' +
            '<td><select class="form-select form-select-sm item-product" name="items[' + rowIndex + '][product_id]" required>' + buildProductOptions() + '</select></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" name="items[' + rowIndex + '][quantity]" min="1" value="1" required></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" name="items[' + rowIndex + '][unit_price]" min="0" step="0.01" value="0" required></td>' +
            '<td class="gst-col" ' + gstDisplay + '><input type="number" class="form-control form-control-sm item-gst" name="items[' + rowIndex + '][gst_percent]" min="0" max="100" step="0.01" value="18"></td>' +
            '<td><input type="number" class="form-control form-control-sm item-discount" name="items[' + rowIndex + '][discount]" min="0" step="0.01" value="0"></td>' +
            '<td><input type="number" class="form-control form-control-sm" name="items[' + rowIndex + '][warranty_months]" min="0" value="0"></td>' +
            '<td><input type="text" class="form-control form-control-sm item-serials" name="items[' + rowIndex + '][serials]" placeholder="Select product first" disabled></td>' +
            '<td class="line-total fw-semibold">₹0.00</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x-lg"></i></button></td>' +
            '</tr>';
        $('#itemsBody').append(html);
        rowIndex++;
    }

    function calculateTotals() {
        var subtotal = 0;
        var gstTotal = 0;
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

    // Customer change — fetch sites via AJAX
    $('#customer_id').on('change', function() {
        var customerId = $(this).val();
        var $siteSelect = $('#site_id');
        $siteSelect.html('<option value="">— Loading Sites... —</option>');

        if (!customerId) {
            $siteSelect.html('<option value="">— Select Customer First —</option>');
            return;
        }

        var url = "{{ route('api.customer.sites', ':id') }}".replace(':id', customerId);
        $.get(url, function(data) {
            var opts = '<option value="">— Select Site —</option>';
            $.each(data, function(i, site) {
                opts += '<option value="' + site.id + '">' + site.name + '</option>';
            });
            $siteSelect.html(opts);
        }).fail(function() {
            $siteSelect.html('<option value="">— Failed to load sites —</option>');
        });
    });

    // Product change — fetch available serials
    $(document).on('change', '.item-product', function() {
        var $row = $(this).closest('tr');
        var productId = $(this).val();
        var isSerialized = $(this).find(':selected').data('serialized');
        var $serialInput = $row.find('.item-serials');

        if (isSerialized && productId) {
            $serialInput.prop('disabled', false).attr('placeholder', 'Loading serials...');
            var url = "{{ route('api.product.serials', ':id') }}".replace(':id', productId);
            $.get(url, function(data) {
                if (data.length) {
                    var serials = data.map(function(s) { return s.serial_number; }).join(', ');
                    $serialInput.attr('placeholder', 'Available: ' + serials);
                } else {
                    $serialInput.attr('placeholder', 'No serials in stock');
                }
            }).fail(function() {
                $serialInput.attr('placeholder', 'Enter serials comma-separated');
            });
        } else {
            $serialInput.prop('disabled', true).val('').attr('placeholder', 'N/A — not serialized');
        }
    });

    // GST toggle
    $('#is_gst').on('change', function() {
        if ($(this).is(':checked')) {
            $('.gst-col').show();
            $('.gst-row').show();
        } else {
            $('.gst-col').hide();
            $('.gst-row').hide();
        }
        calculateTotals();
    });

    // Add / remove items
    $('#addItemBtn').on('click', function() {
        addItemRow();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    // Recalculate on input
    $(document).on('input', '.item-qty, .item-price, .item-gst, .item-discount, #discount', function() {
        calculateTotals();
    });

    // Init: trigger GST toggle state
    $('#is_gst').trigger('change');

    // Add first row
    addItemRow();

    // If customer was pre-selected (old input), trigger site load
    @if(old('customer_id'))
        $('#customer_id').trigger('change');
    @endif
});
</script>
@endsection
