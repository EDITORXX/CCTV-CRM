@extends('layouts.app')

@section('title', 'Edit Purchase')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Purchase</h4>
        <p class="text-muted mb-0">Update bill details for {{ $purchase->bill_number }}</p>
    </div>
    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('purchases.update', $purchase) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="vendor_id" class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id" required>
                        <option value="">— Select Vendor —</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $purchase->vendor_id) == $vendor->id ? 'selected' : '' }}>
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
                           id="bill_number" name="bill_number" value="{{ old('bill_number', $purchase->bill_number) }}" required>
                    @error('bill_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="bill_date" class="form-label">Bill Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('bill_date') is-invalid @enderror"
                           id="bill_date" name="bill_date" value="{{ old('bill_date', $purchase->bill_date) }}" required>
                    @error('bill_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="3">{{ old('notes', $purchase->notes) }}</textarea>
                    @error('notes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Purchase
                </button>
                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
