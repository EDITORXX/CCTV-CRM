@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Invoice</h4>
        <p class="text-muted mb-0">Update invoice {{ $invoice->invoice_number }}</p>
    </div>
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('invoices.update', $invoice) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="draft" {{ old('status', $invoice->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ old('status', $invoice->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ old('status', $invoice->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="4">{{ old('notes', $invoice->notes) }}</textarea>
                    @error('notes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Invoice
                </button>
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
