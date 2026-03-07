@extends('layouts.app')

@section('title', 'Support Videos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Support Videos</h4>
        <p class="text-muted mb-0">Manage video tutorials for customers (brand-wise)</p>
    </div>
    <a href="{{ route('support-videos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Video
    </a>
</div>

{{-- Desktop table view --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="videosTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th width="100">Thumbnail</th>
                        <th>Title</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($videos as $video)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($video->thumbnail_url)
                                <img src="{{ $video->thumbnail_url }}" alt="" class="rounded" width="80" height="45" style="object-fit:cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:80px;height:45px;">
                                    <i class="bi bi-play-circle text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ Str::limit($video->title, 45) }}</td>
                        <td>{{ $video->brand ?? '—' }}</td>
                        <td>{{ $video->category ?? '—' }}</td>
                        <td>
                            @if($video->is_youtube)
                                <span class="badge bg-danger"><i class="bi bi-youtube me-1"></i>YouTube</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-upload me-1"></i>Uploaded</span>
                            @endif
                        </td>
                        <td>
                            @if($video->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td>{{ $video->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('support-videos.edit', $video) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('support-videos.destroy', $video) }}" method="POST"
                                      onsubmit="return confirm('Delete this video?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Mobile card view --}}
<div class="d-md-none">
    @forelse($videos as $video)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="d-flex gap-3 align-items-start">
                <div class="flex-shrink-0">
                    @if($video->thumbnail_url)
                        <img src="{{ $video->thumbnail_url }}" alt="" class="rounded" width="72" height="48" style="object-fit:cover;">
                    @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:72px;height:48px;">
                            <i class="bi bi-play-circle text-muted fs-4"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-grow-1 min-w-0">
                    <h6 class="fw-semibold mb-1 text-truncate">{{ $video->title }}</h6>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @if($video->brand)
                            <span class="badge bg-secondary" style="font-size:.7rem;">{{ $video->brand }}</span>
                        @endif
                        @if($video->category)
                            <span class="badge bg-light text-dark border" style="font-size:.7rem;">{{ $video->category }}</span>
                        @endif
                        @if($video->is_youtube)
                            <span class="badge bg-danger" style="font-size:.7rem;"><i class="bi bi-youtube me-1"></i>YouTube</span>
                        @else
                            <span class="badge bg-secondary" style="font-size:.7rem;"><i class="bi bi-upload me-1"></i>Uploaded</span>
                        @endif
                        @if($video->is_published)
                            <span class="badge bg-success" style="font-size:.7rem;">Published</span>
                        @else
                            <span class="badge bg-secondary" style="font-size:.7rem;">Draft</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">{{ $video->created_at->format('d M Y') }}</small>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('support-videos.edit', $video) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('support-videos.destroy', $video) }}" method="POST"
                                  onsubmit="return confirm('Delete this video?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-camera-video fs-1 d-block mb-2"></i>
        <p>No videos yet. <a href="{{ route('support-videos.create') }}">Add your first video</a>.</p>
    </div>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#videosTable').DataTable({
            paging: true,
            pageLength: 25,
            order: [[7, 'desc']],
            columnDefs: [
                { orderable: false, targets: [1, 8] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-camera-video fs-1 d-block mb-2"></i>No videos yet. <a href="{{ route('support-videos.create') }}">Add your first video</a>.</div>'
            }
        });
    });
</script>
@endsection
