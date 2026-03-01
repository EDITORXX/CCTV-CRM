@extends('layouts.app')

@section('title', 'Troubleshoot — Ended')

@section('content')
<div class="text-center py-5">
    <i class="bi bi-camera-video-off text-muted" style="font-size: 3rem;"></i>
    <h4 class="mt-3 fw-bold">Session Ended</h4>
    <p class="text-muted">The customer has ended the troubleshoot session.</p>
    <a href="{{ route('troubleshoot.connect') }}" class="btn btn-primary">Connect to Another Customer</a>
</div>
@endsection
