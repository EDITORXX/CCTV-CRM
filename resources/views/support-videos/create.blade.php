@extends('layouts.app')

@section('title', 'Add Video')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Add Support Video</h4>
        <p class="text-muted mb-0">Add a tutorial video (YouTube link or upload file)</p>
    </div>
    <a href="{{ route('support-videos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('support-videos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title') }}"
                           placeholder="e.g. How to set date & time on Hikvision DVR" required>
                    @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control @error('brand') is-invalid @enderror"
                           id="brand" name="brand" value="{{ old('brand') }}"
                           placeholder="e.g. Hikvision, Dahua, CP Plus" list="brandList">
                    <datalist id="brandList">
                        @foreach($brands as $b)
                            <option value="{{ $b }}">
                        @endforeach
                    </datalist>
                    @error('brand') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <input type="text" class="form-control @error('category') is-invalid @enderror"
                           id="category" name="category" value="{{ old('category') }}"
                           placeholder="e.g. Setup, Troubleshooting, Mobile App" list="categoryList">
                    <datalist id="categoryList">
                        <option value="Setup">
                        <option value="Troubleshooting">
                        <option value="Mobile App">
                        <option value="Configuration">
                        <option value="Installation">
                    </datalist>
                    @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Video Source <span class="text-danger">*</span></label>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#urlTab" type="button">
                                <i class="bi bi-youtube me-1"></i> YouTube / URL
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#uploadTab" type="button">
                                <i class="bi bi-upload me-1"></i> Upload File
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 rounded-bottom p-3">
                        <div class="tab-pane fade show active" id="urlTab">
                            <input type="url" class="form-control @error('video_url') is-invalid @enderror"
                                   name="video_url" value="{{ old('video_url') }}"
                                   placeholder="https://www.youtube.com/watch?v=...">
                            @error('video_url') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="tab-pane fade" id="uploadTab">
                            <input type="file" class="form-control @error('video_file') is-invalid @enderror"
                                   name="video_file" accept="video/mp4,video/webm,video/quicktime">
                            <small class="text-muted">Max 100 MB. Allowed: MP4, WebM, MOV</small>
                            @error('video_file') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="thumbnail_file" class="form-label">Thumbnail (optional)</label>
                    <input type="file" class="form-control @error('thumbnail_file') is-invalid @enderror"
                           id="thumbnail_file" name="thumbnail_file" accept="image/*">
                    <small class="text-muted">Auto-fetched for YouTube links if not provided</small>
                    @error('thumbnail_file') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3"
                              placeholder="Brief description of the video...">{{ old('description') }}</textarea>
                    @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                               {{ old('is_published', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published">Publish immediately</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Add Video
                </button>
                <a href="{{ route('support-videos.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
