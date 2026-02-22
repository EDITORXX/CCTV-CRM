@extends('layouts.app')

@section('title', 'Edit Site Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Site Expense</h4>
        <p class="text-muted mb-0">Update expense details</p>
    </div>
    <a href="{{ route('site-expenses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('site-expenses.update', $site_expense) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Customer</label>
                    <input type="text" class="form-control" value="{{ $site_expense->customer->name ?? '-' }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Site</label>
                    <input type="text" class="form-control" value="{{ $site_expense->site->site_name ?? '-' }}" disabled>
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                              rows="3" required>{{ old('description', $site_expense->description) }}</textarea>
                    @error('description')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="amount" class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount"
                           min="0" step="0.01" value="{{ old('amount', $site_expense->amount) }}" required>
                    @error('amount')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date"
                           value="{{ old('expense_date', $site_expense->expense_date->format('Y-m-d')) }}" required>
                    @error('expense_date')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label for="technician_name" class="form-label">Technician Name</label>
                    <input type="text" class="form-control @error('technician_name') is-invalid @enderror" id="technician_name" name="technician_name"
                           value="{{ old('technician_name', $site_expense->technician_name) }}">
                    @error('technician_name')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Expense
                </button>
                <a href="{{ route('site-expenses.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
