@extends('layouts.app')

@section('title', 'New Task')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">New Task</h4>
        <p class="text-muted mb-0">{{ $userRole === 'technician' ? 'Create a task for yourself' : 'Create and assign a task to a technician' }}</p>
    </div>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title') }}"
                           placeholder="Enter task title..." required>
                    @error('title')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="task_category_id" class="form-label">Category</label>
                    <div class="input-group">
                        <select class="form-select @error('task_category_id') is-invalid @enderror"
                                id="task_category_id" name="task_category_id">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('task_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Add New Category">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    @error('task_category_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                @if($userRole !== 'technician')
                <div class="col-md-6">
                    <label for="assigned_to" class="form-label">Assign To <span class="text-danger">*</span></label>
                    <select class="form-select @error('assigned_to') is-invalid @enderror"
                            id="assigned_to" name="assigned_to" required>
                        <option value="">— Select Technician —</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('assigned_to') == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }} ({{ $tech->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="col-md-{{ $userRole !== 'technician' ? '3' : '6' }}">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                           id="due_date" name="due_date" value="{{ old('due_date') }}">
                    @error('due_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-{{ $userRole !== 'technician' ? '3' : '6' }}">
                    <label for="reminder_date" class="form-label">Reminder Date & Time</label>
                    <input type="datetime-local" class="form-control @error('reminder_date') is-invalid @enderror"
                           id="reminder_date" name="reminder_date" value="{{ old('reminder_date') }}">
                    <small class="text-muted">Email reminder will be sent at this time</small>
                    @error('reminder_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="4"
                              placeholder="Add any notes or details about this task...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Task
                </button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('task-categories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_redirect" value="{{ route('tasks.create') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Add Task Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="name"
                               placeholder="e.g. Maintenance" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
