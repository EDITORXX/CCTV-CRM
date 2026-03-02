@extends('layouts.app')

@section('title', 'Estimate Templates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Estimate Templates</h4>
        <p class="text-muted mb-0">Reusable estimate templates (e.g. 4 Cam / 8 Cam setup)</p>
    </div>
    <a href="{{ route('estimates.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Estimates
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@forelse($templates as $template)
<div class="card border-0 shadow-sm mb-3 overflow-hidden">
    <div class="d-flex">
        <div class="flex-shrink-0" style="width:5px; background:#0d6efd;"></div>
        <div class="flex-grow-1 p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="fw-bold">{{ $template->name }}</div>
                <div class="fw-bold fs-6">₹{{ number_format($template->total, 2) }}</div>
            </div>
            <div class="text-muted small mb-2">{{ $template->items->count() }} item(s)</div>
            <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                <a href="{{ route('quotation-templates.show', $template) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View
                </a>
                <a href="{{ route('quotation-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <a href="{{ route('quotation-templates.pdf', $template) }}" class="btn btn-sm btn-outline-info" target="_blank">
                    <i class="bi bi-file-pdf me-1"></i> PDF
                </a>
                <form action="{{ route('quotation-templates.to-estimate', $template) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-arrow-repeat me-1"></i> New Estimate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-file-earmark-text display-4 d-block mb-2"></i>
        No estimate templates yet. Run the seeder to add 4 Cam / 8 Cam templates.
    </div>
</div>
@endforelse
@endsection
