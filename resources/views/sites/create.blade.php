@extends('layouts.app')

@section('title', 'Add Site — ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Add Site</h4>
        <p class="text-muted mb-0">
            Customer: <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none">{{ $customer->name }}</a>
        </p>
    </div>
    <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('customers.sites.store', $customer) }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Site Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="contact_person" class="form-label">Contact Person</label>
                    <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
                           id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                    @error('contact_person')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="contact_phone" class="form-label">Contact Phone</label>
                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror"
                           id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}">
                    @error('contact_phone')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">&nbsp;</div>

                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror"
                              id="address" name="address" rows="3">{{ old('address') }}</textarea>
                    @error('address')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Site
                </button>
                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
