@extends('layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Vendor</h4>
        <p class="text-muted mb-0">Update vendor: <strong>{{ $vendor->name }}</strong></p>
    </div>
    <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('vendors.update', $vendor) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $vendor->name) }}" required>
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                           id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}">
                    @error('phone')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email', $vendor->email) }}">
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="gstin" class="form-label">GSTIN</label>
                    <input type="text" class="form-control @error('gstin') is-invalid @enderror"
                           id="gstin" name="gstin" value="{{ old('gstin', $vendor->gstin) }}" maxlength="15">
                    @error('gstin')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror"
                              id="address" name="address" rows="3">{{ old('address', $vendor->address) }}</textarea>
                    @error('address')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Vendor
                </button>
                <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
