@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Product</h4>
        <p class="text-muted mb-0">Update product: <strong>{{ $product->name }}</strong></p>
    </div>
    <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $product->name) }}" required>
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                        <option value="">Select Category</option>
                        @foreach(['Camera', 'DVR/NVR', 'HDD', 'Cable', 'SMPS', 'Accessories', 'Other'] as $cat)
                            <option value="{{ $cat }}" {{ old('category', $product->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control @error('brand') is-invalid @enderror"
                           id="brand" name="brand" value="{{ old('brand', $product->brand) }}">
                    @error('brand')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="model_number" class="form-label">Model Number</label>
                    <input type="text" class="form-control @error('model_number') is-invalid @enderror"
                           id="model_number" name="model_number" value="{{ old('model_number', $product->model_number) }}">
                    @error('model_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="hsn_sac" class="form-label">HSN/SAC Code</label>
                    <input type="text" class="form-control @error('hsn_sac') is-invalid @enderror"
                           id="hsn_sac" name="hsn_sac" value="{{ old('hsn_sac', $product->hsn_sac) }}">
                    @error('hsn_sac')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                    <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                        <option value="pcs" {{ old('unit', $product->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                        <option value="meter" {{ old('unit', $product->unit) == 'meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                    @error('unit')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="warranty_months" class="form-label">Warranty (Months)</label>
                    <input type="number" class="form-control @error('warranty_months') is-invalid @enderror"
                           id="warranty_months" name="warranty_months" value="{{ old('warranty_months', $product->warranty_months) }}" min="0">
                    @error('warranty_months')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="sale_price" class="form-label">Sale Price</label>
                    <div class="input-group">
                        <span class="input-group-text">&#8377;</span>
                        <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror"
                               id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" min="0">
                    </div>
                    @error('sale_price')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label d-block">&nbsp;</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="track_serial" name="track_serial" value="1"
                               {{ old('track_serial', $product->track_serial) ? 'checked' : '' }}>
                        <label class="form-check-label" for="track_serial">Track Serial Numbers</label>
                    </div>
                    @error('track_serial')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Product
                </button>
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
