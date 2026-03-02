@extends('layouts.app')

@section('title', 'Estimates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Estimates</h4>
        <p class="text-muted mb-0">Manage estimates for customers</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('quotation-templates.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-ruled me-1"></i> Templates
        </a>
        <a href="{{ route('estimates.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Estimate
        </a>
    </div>
</div>

@forelse($estimates as $estimate)
<div class="card border-0 shadow-sm mb-3 overflow-hidden">
    <div class="d-flex">
        <div class="flex-shrink-0" style="width:5px;
            @if($estimate->status === 'draft') background:#6c757d;
            @elseif($estimate->status === 'sent') background:#0dcaf0;
            @elseif($estimate->status === 'accepted') background:#198754;
            @elseif($estimate->status === 'rejected') background:#dc3545;
            @elseif($estimate->status === 'converted') background:#0d6efd;
            @else background:#adb5bd;
            @endif
        "></div>
        <div class="flex-grow-1 p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="text-muted small">{{ $estimate->estimate_date->format('d-m-Y') }}</span>
                <span class="badge
                    @if($estimate->status === 'draft') bg-secondary
                    @elseif($estimate->status === 'sent') bg-info
                    @elseif($estimate->status === 'accepted') bg-success
                    @elseif($estimate->status === 'rejected') bg-danger
                    @elseif($estimate->status === 'converted') bg-primary
                    @endif
                ">{{ ucfirst($estimate->status) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <div class="fw-bold">
                        {{ $estimate->customer_display_name }}
                        @if($estimate->isWalkIn())
                            <span class="badge bg-warning text-dark" style="font-size:0.6em;">Walk-in</span>
                        @endif
                    </div>
                    <div class="text-muted small">{{ $estimate->estimate_number }}</div>
                    @if($estimate->site)
                        <div class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $estimate->site->site_name ?? '' }}</div>
                    @endif
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-6">₹{{ number_format($estimate->total, 2) }}</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                <a href="{{ route('estimates.show', $estimate) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View
                </a>
                @if(!$estimate->isConverted())
                    <a href="{{ route('estimates.edit', $estimate) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ route('estimates.show', $estimate) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-arrow-repeat me-1"></i> Convert to Invoice
                    </a>
                @else
                    <span class="btn btn-sm btn-outline-primary disabled">
                        <i class="bi bi-check-circle me-1"></i> Converted
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
        No estimates yet. <a href="{{ route('estimates.create') }}">Create your first estimate</a>.
    </div>
</div>
@endforelse

<div class="mt-3">
    {{ $estimates->links() }}
</div>
@endsection
