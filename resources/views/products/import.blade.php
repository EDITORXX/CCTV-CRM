@extends('layouts.app')

@section('title', 'Import Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Import Products from Excel</h4>
        <p class="text-muted mb-0">Upload an Excel file to add multiple products at once</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="mb-3">
            <a href="{{ route('products.import.template') }}" class="btn btn-outline-primary">
                <i class="bi bi-download me-1"></i> Download Sample Template
            </a>
            <span class="text-muted small ms-2">Use the template to see the exact column format. Category: Camera, DVR_NVR, HDD, Cable, SMPS, Accessories, Other. Unit: pcs or meter. track_serial: 1 or 0.</span>
        </p>

        @if(session('import_errors'))
            <div class="alert alert-danger">
                <strong>Import failed. Please fix the following and try again:</strong>
                <ul class="mb-0 mt-2">
                    @foreach(session('import_errors') as $messages)
                        @if(is_array($messages))
                            @foreach($messages as $m)
                                <li>{{ $m }}</li>
                            @endforeach
                        @else
                            <li>{{ $messages }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">Select Excel file (.xlsx, .xls)</label>
                <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls" required>
                @error('file')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload me-1"></i> Import
            </button>
        </form>
    </div>
</div>
@endsection
