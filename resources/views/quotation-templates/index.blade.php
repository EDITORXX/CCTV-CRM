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

<div class="row g-3">
    @forelse($templates as $template)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title">{{ $template->name }}</h5>
                <p class="text-muted small mb-2">{{ $template->items->count() }} item(s) · Total ₹{{ number_format($template->total, 2) }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('quotation-templates.show', $template) }}" class="btn btn-sm btn-outline-primary">View</a>
                    <a href="{{ route('quotation-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    <a href="{{ route('quotation-templates.pdf', $template) }}" class="btn btn-sm btn-outline-info" target="_blank">PDF</a>
                    <form action="{{ route('quotation-templates.to-estimate', $template) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">New estimate from template</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-file-earmark-text display-4 d-block mb-2"></i>
                No estimate templates yet. Run the seeder to add 4 Cam / 8 Cam templates.
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
