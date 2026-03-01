@extends('layouts.app')

@section('title', 'Troubleshoot — Connect to Customer')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><i class="bi bi-tools me-1"></i> Connect to Customer</h4>
            <a href="{{ route('livestream.index') }}" class="btn btn-outline-secondary btn-sm">Back to CCTV View</a>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="text-muted small mb-3">Ask the customer to open <strong>Portal → Troubleshoot</strong> and share the 6-character code and 4-digit PIN with you.</p>
                @if($errors->any())
                    <div class="alert alert-danger small py-2 mb-3">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('troubleshoot.verify') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="code" class="form-label">Code <span class="text-muted">(6 characters)</span></label>
                        <input type="text" class="form-control form-control-lg text-uppercase" id="code" name="code"
                               maxlength="6" placeholder="e.g. AB12CD" value="{{ old('code') }}" required
                               style="letter-spacing: .2em;">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">PIN <span class="text-muted">(4 digits)</span></label>
                        <input type="text" class="form-control form-control-lg" id="password" name="password"
                               maxlength="4" placeholder="e.g. 7890" value="{{ old('password') }}" required
                               pattern="[0-9]{4}" inputmode="numeric">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-camera-video me-1"></i> Connect & View
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
