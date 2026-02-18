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
            <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="250">Product</th>
                            <th width="80">Qty</th>
                            <th width="120">Unit Price</th>
                            <th width="80">GST%</th>
                            <th>Serial Numbers</th>
                            <th width="120">Line Total</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                            <td class="fw-bold text-success" id="grandTotal">₹0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
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
<script>
$(document).ready(function() {
    var rowIndex = 0;

    var products = @json($products->map(function($p) {
        return ['id' => $p->id, 'name' => $p->name, 'category' => $p->category->name ?? ''];
    }));

    function buildProductOptions() {
        var opts = '<option value="">— Select Product —</option>';
        products.forEach(function(p) {
            var label = p.name + (p.category ? ' [' + p.category + ']' : '');
            opts += '<option value="' + p.id + '">' + label + '</option>';
        });
        return opts;
    }

    function addItemRow() {
        var html = '<tr data-row="' + rowIndex + '">' +
            '<td><select class="form-select form-select-sm" name="items[' + rowIndex + '][product_id]" required>' + buildProductOptions() + '</select></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" name="items[' + rowIndex + '][quantity]" min="1" value="1" required></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" name="items[' + rowIndex + '][unit_price]" min="0" step="0.01" value="0" required></td>' +
            '<td><input type="number" class="form-control form-control-sm item-gst" name="items[' + rowIndex + '][gst_percent]" min="0" max="100" step="0.01" value="18" required></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items[' + rowIndex + '][serials]" placeholder="SN1, SN2, ..."></td>' +
            '<td class="line-total fw-semibold">₹0.00</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x-lg"></i></button></td>' +
            '</tr>';
        $('#itemsBody').append(html);
        rowIndex++;
    }

    function calculateTotals() {
        var grandTotal = 0;
        $('#itemsBody tr').each(function() {
            var qty = parseFloat($(this).find('.item-qty').val()) || 0;
            var price = parseFloat($(this).find('.item-price').val()) || 0;
            var gst = parseFloat($(this).find('.item-gst').val()) || 0;
            var lineTotal = qty * price * (1 + gst / 100);
            $(this).find('.line-total').text('₹' + lineTotal.toFixed(2));
            grandTotal += lineTotal;
        });
        $('#grandTotal').text('₹' + grandTotal.toFixed(2));
    }

    $('#addItemBtn').on('click', function() {
        addItemRow();
    });

    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    $(document).on('input', '.item-qty, .item-price, .item-gst', function() {
        calculateTotals();
    });

    addItemRow();
});
</script>
@endsection
