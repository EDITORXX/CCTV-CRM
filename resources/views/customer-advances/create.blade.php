@extends('layouts.app')

@section('title', 'Record Advance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Record Customer Advance</h4>
        <p class="text-muted mb-0">Receive advance and generate receipt; adjust in invoices later</p>
    </div>
    <a href="{{ route('customer-advances.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-cash-stack me-1"></i> Advance Details
    </div>
    <div class="card-body">
        <form action="{{ route('customer-advances.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                        <option value="">— Select Customer —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} — {{ $c->phone ?? 'No phone' }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label for="receipt_number" class="form-label">Receipt Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" id="receipt_number" name="receipt_number"
                           value="{{ old('receipt_number', $nextNumber ?? '') }}" required>
                    @error('receipt_number')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label for="amount" class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount"
                           min="0.01" step="0.01" value="{{ old('amount') }}" required>
                    @error('amount')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date"
                           value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    @error('payment_date')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                    </select>
                    @error('payment_method')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="reference_number" class="form-label">Reference / Transaction ID</label>
                    <input type="text" class="form-control @error('reference_number') is-invalid @enderror" id="reference_number" name="reference_number"
                           value="{{ old('reference_number') }}" placeholder="Optional">
                    @error('reference_number')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2" placeholder="Optional">{{ old('notes') }}</textarea>
                    @error('notes')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Record Advance & Generate Receipt
                    </button>
                    <a href="{{ route('customer-advances.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
