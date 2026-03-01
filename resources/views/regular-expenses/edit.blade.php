@extends('layouts.app')

@section('title', 'Edit Regular Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Regular Expense</h4>
        <p class="text-muted mb-0">Update expense details</p>
    </div>
    <a href="{{ route('regular-expenses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('regular-expenses.update', $regular_expense) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="expense_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select @error('expense_category_id') is-invalid @enderror" id="expense_category_id" name="expense_category_id" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('expense_category_id', $regular_expense->expense_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('expense_category_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount"
                           min="0" step="0.01" value="{{ old('amount', $regular_expense->amount) }}" required>
                    @error('amount')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                              rows="2">{{ old('description', $regular_expense->description) }}</textarea>
                    @error('description')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-6">
                    <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('expense_date') is-invalid @enderror" id="expense_date" name="expense_date"
                           value="{{ old('expense_date', $regular_expense->expense_date->format('Y-m-d')) }}" required>
                    @error('expense_date')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Expense
                </button>
                <a href="{{ route('regular-expenses.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
