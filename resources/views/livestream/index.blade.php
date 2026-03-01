@extends('layouts.app')

@section('title', 'Live Streams')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Live Streams</h4>
    <a href="{{ route('livestream.create') }}" class="btn btn-primary">
        <i class="bi bi-broadcast-pin me-1"></i> Start New Stream
    </a>
</div>

@if($streams->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-broadcast-pin text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">No live streams yet</h5>
            <p class="text-muted">Connect your USB capture card and start streaming DVR footage to anyone.</p>
            <a href="{{ route('livestream.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-broadcast-pin me-1"></i> Start Your First Stream
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
                                    <i class="bi bi-broadcast me-1"></i> Open
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
