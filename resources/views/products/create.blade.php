@extends('layouts.app')

@section('title', 'Add Product / Service')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Add Product / Service</h4>
        <p class="text-muted mb-0">Create a new product or service</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf

            {{-- Type selector --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="typeProduct" value="product"
                               {{ old('type', request('type', 'product')) === 'product' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="typeProduct">
                            <i class="bi bi-box-seam me-1 text-primary"></i> Product (Hardware)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="typeService" value="service"
                               {{ old('type', request('type')) === 'service' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="typeService">
                            <i class="bi bi-tools me-1 text-success"></i> Service (Installation / Repair etc.)
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. Installation Charges / CP Plus 2MP Camera">
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Product categories --}}
                <div class="col-md-6" id="productCategoryGroup">
                    <label for="category_product" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category_product" name="category">
                        <option value="">Select Category</option>
                        @foreach(['Camera', 'DVR_NVR', 'HDD', 'Cable', 'SMPS', 'Accessories', 'IP', 'Analog', 'Other'] as $cat)
                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ str_replace('_', '/', $cat) }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Service categories --}}
                <div class="col-md-6 d-none" id="serviceCategoryGroup">
                    <label for="category_service" class="form-label">Service Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category_service" name="category">
                        <option value="">Select Type</option>
                        @foreach(['Installation' => 'Installation', 'Repair' => 'Repair', 'Cabling' => 'Cabling', 'AMC' => 'AMC (Annual Maintenance)', 'Other_Service' => 'Other Service'] as $val => $label)
                            <option value="{{ $val }}" {{ old('category') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Product-only fields --}}
                <div class="col-md-4" id="brandGroup">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control @error('brand') is-invalid @enderror"
                           id="brand" name="brand" value="{{ old('brand') }}" placeholder="e.g. Hikvision, CP Plus">
                    @error('brand')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4" id="modelGroup">
                    <label for="model_number" class="form-label">Model Number</label>
                    <input type="text" class="form-control @error('model_number') is-invalid @enderror"
                           id="model_number" name="model_number" value="{{ old('model_number') }}">
                    @error('model_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="hsn_sac" class="form-label">HSN/SAC Code</label>
                    <input type="text" class="form-control @error('hsn_sac') is-invalid @enderror"
                           id="hsn_sac" name="hsn_sac" value="{{ old('hsn_sac') }}">
                    @error('hsn_sac')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4" id="unitGroup">
                    <label for="unit" class="form-label">Unit</label>
                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit">
                        <option value="pcs" {{ old('unit', 'pcs') == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                        <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                    @error('unit')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4" id="warrantyGroup">
                    <label for="warranty_months" class="form-label">Warranty (Months)</label>
                    <input type="number" class="form-control @error('warranty_months') is-invalid @enderror"
                           id="warranty_months" name="warranty_months" value="{{ old('warranty_months', 0) }}" min="0">
                    @error('warranty_months')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="sale_price" class="form-label" id="salePriceLabel">Sale Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror"
                               id="sale_price" name="sale_price" value="{{ old('sale_price', '0.00') }}" min="0">
                    </div>
                    @error('sale_price')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4" id="trackSerialGroup">
                    <label class="form-label d-block">&nbsp;</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="track_serial" name="track_serial" value="1"
                               {{ old('track_serial') ? 'checked' : '' }}>
                        <label class="form-check-label" for="track_serial">Track Serial Numbers</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="bi bi-check-lg me-1"></i> Save Product
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var initialType = $('input[name="type"]:checked').val();
    toggleType(initialType);

    $('input[name="type"]').on('change', function() {
        toggleType($(this).val());
    });

    // Sync category selects
    $('#category_service').on('change', function() {
        $('#category_product').val($(this).val());
    });
    $('#category_product').on('change', function() {
        $('#category_service').val($(this).val());
    });
    function toggleType(type) {
        if (type === 'service') {
            $('#productCategoryGroup').addClass('d-none');
            $('#category_product').prop('disabled', true);
            $('#serviceCategoryGroup').removeClass('d-none');
            $('#brandGroup, #modelGroup, #warrantyGroup, #trackSerialGroup, #unitGroup').addClass('d-none');
            $('#salePriceLabel').text('Standard Charge (₹)');
            $('#submitBtn').html('<i class="bi bi-check-lg me-1"></i> Save Service');
        } else {
            $('#productCategoryGroup').removeClass('d-none');
            $('#category_product').prop('disabled', false);
            $('#serviceCategoryGroup').addClass('d-none');
            $('#brandGroup, #modelGroup, #warrantyGroup, #trackSerialGroup, #unitGroup').removeClass('d-none');
            $('#salePriceLabel').text('Sale Price');
            $('#submitBtn').html('<i class="bi bi-check-lg me-1"></i> Save Product');
        }
    }
});
</script>
@endsection
