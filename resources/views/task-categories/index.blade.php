@extends('layouts.app')

@section('title', 'Task Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Task Categories</h4>
        <p class="text-muted mb-0">Manage task categories (e.g. Service, Site Visit, Tailing)</p>
    </div>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Tasks
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Add Category</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('task-categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                               value="{{ old('name') }}" placeholder="e.g. Maintenance" required>
                        @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Current Categories</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Tasks</th>
                                <th>Type</th>
                                <th width="80">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $index => $cat)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $cat->name }}</td>
                                <td>{{ $cat->tasks_count }}</td>
                                <td>
                                    @if($cat->is_default)
                                        <span class="badge bg-info bg-opacity-10 text-info">Default</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Custom</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$cat->is_default)
                                    <form action="{{ route('task-categories.destroy', $cat) }}" method="POST"
                                          onsubmit="return confirm('Delete this category? Tasks in this category will become uncategorized.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-muted" title="Default categories cannot be deleted">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No categories yet. Add one using the form.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
