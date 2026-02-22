@extends('layouts.app')

@section('title', 'Edit Estimate')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Estimate {{ $estimate->estimate_number }}</h4>
        <p class="text-muted mb-0">Update estimate status and details</p>
    </div>
    <a href="{{ route('estimates.show', $estimate) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil me-1"></i> Update Details</div>
            <div class="card-body">
                <form action="{{ route('estimates.update', $estimate) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="draft" {{ $estimate->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ $estimate->status === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="accepted" {{ $estimate->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="rejected" {{ $estimate->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="valid_until" class="form-label">Valid Until</label>
                            <input type="date" class="form-control" name="valid_until" id="valid_until"
                                   value="{{ $estimate->valid_until ? $estimate->valid_until->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="3">{{ $estimate->notes }}</textarea>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Update Estimate</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-list-ul me-1"></i> Line Items (Read Only)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estimate->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->product->name }}</td>
                                <td class="text-center">{{ $item->qty }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-calculator me-1"></i> Summary</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">Subtotal</td><td class="text-end fw-semibold">₹{{ number_format($estimate->subtotal, 2) }}</td></tr>
                    @if($estimate->is_gst)
                    <tr><td class="text-muted">GST</td><td class="text-end fw-semibold">₹{{ number_format($estimate->gst_amount, 2) }}</td></tr>
                    @endif
                    @if($estimate->discount > 0)
                    <tr><td class="text-muted">Discount</td><td class="text-end text-danger">-₹{{ number_format($estimate->discount, 2) }}</td></tr>
                    @endif
                    <tr class="border-top">
                        <td class="fw-bold fs-5">Total</td>
                        <td class="text-end fw-bold fs-5 text-success">₹{{ number_format($estimate->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
