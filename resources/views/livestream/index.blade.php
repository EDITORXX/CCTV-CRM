@extends('layouts.app')

@section('title', 'CCTV View')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0">CCTV View</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('troubleshoot.connect') }}" class="btn btn-outline-primary">
            <i class="bi bi-tools me-1"></i> Connect to Customer (Troubleshoot)
        </a>
        <a href="{{ route('livestream.create') }}" class="btn btn-primary">
            <i class="bi bi-camera-video me-1"></i> Start CCTV View
        </a>
    </div>
</div>

@if($streams->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-camera-video text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">No CCTV streams yet</h5>
            <p class="text-muted">Connect your USB capture card and stream DVR footage to viewers.</p>
            <a href="{{ route('livestream.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-camera-video me-1"></i> Start Your First CCTV View
            </a>
        </div>
    </div>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Started</th>
                        <th>Ended</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($streams as $stream)
                    <tr>
                        <td>{{ $stream->id }}</td>
                        <td>{{ $stream->title ?: 'Untitled Stream' }}</td>
                        <td>
                            @if($stream->isActive())
                                <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size:.5rem;vertical-align:middle;"></i> Live</span>
                            @else
                                <span class="badge bg-secondary">Ended</span>
                            @endif
                        </td>
                        <td>{{ $stream->started_at->format('d M Y, h:i A') }}</td>
                        <td>{{ $stream->ended_at ? $stream->ended_at->format('d M Y, h:i A') : '—' }}</td>
                        <td>
                            @if($stream->isActive())
                                <a href="{{ route('livestream.show', $stream) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-camera-video me-1"></i> Open
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $streams->links() }}
    </div>
@endif
@endsection
