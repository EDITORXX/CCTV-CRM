@extends('layouts.app')

@section('title', 'Edit Article')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Article</h4>
        <p class="text-muted mb-0">{{ $supportArticle->title }}</p>
    </div>
    <a href="{{ route('support-articles.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('support-articles.update', $supportArticle) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title', $supportArticle->title) }}" required>
                    @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="faq" {{ old('type', $supportArticle->type) === 'faq' ? 'selected' : '' }}>FAQ</option>
                        <option value="guide" {{ old('type', $supportArticle->type) === 'guide' ? 'selected' : '' }}>Guide / Solution</option>
                    </select>
                    @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label for="brand" class="form-label">Brand</label>
                    <input type="text" class="form-control @error('brand') is-invalid @enderror"
                           id="brand" name="brand" value="{{ old('brand', $supportArticle->brand) }}"
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
                           id="category" name="category" value="{{ old('category', $supportArticle->category) }}"
                           placeholder="e.g. DVR/NVR, Camera, Network" list="categoryList">
                    <datalist id="categoryList">
                        <option value="DVR/NVR">
                        <option value="Camera">
                        <option value="Network">
                        <option value="Setup">
                        <option value="Troubleshooting">
                        <option value="Mobile App">
                    </datalist>
                    @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror"
                              id="content" name="content" rows="12">{{ old('content', $supportArticle->content) }}</textarea>
                    @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1"
                               {{ old('is_published', $supportArticle->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published">Published</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Article
                </button>
                <a href="{{ route('support-articles.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content',
        height: 400,
        menubar: false,
        plugins: 'lists link image code table',
        toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | code',
        content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; }'
    });
</script>
@endsection
