@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $userRole === 'technician' ? 'My Tasks' : 'Tasks' }}</h4>
        <p class="text-muted mb-0">{{ $userRole === 'technician' ? 'Track and manage your tasks' : 'Manage and assign tasks to technicians' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('task-categories.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-tags me-1"></i> Categories
        </a>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Task
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Stats Cards - 2x2 grid on mobile --}}
<div class="row g-2 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-2 py-md-3">
                <div class="fs-3 fs-md-2 fw-bold text-primary">{{ $tasks->count() }}</div>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-2 py-md-3">
                <div class="fs-3 fs-md-2 fw-bold text-warning">{{ $tasks->where('status', 'pending')->count() }}</div>
                <small class="text-muted">Pending</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-2 py-md-3">
                <div class="fs-3 fs-md-2 fw-bold text-info">{{ $tasks->where('status', 'in_progress')->count() }}</div>
                <small class="text-muted">In Progress</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-2 py-md-3">
                <div class="fs-3 fs-md-2 fw-bold text-success">{{ $tasks->where('status', 'completed')->count() }}</div>
                <small class="text-muted">Completed</small>
            </div>
        </div>
    </div>
</div>

{{-- Desktop Table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tasksTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Title</th>
                        <th>Category</th>
                        @if($userRole !== 'technician')
                        <th>Assigned To</th>
                        @endif
                        <th>Due Date</th>
                        <th>Reminder</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th width="160">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $task)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('tasks.show', $task) }}" class="fw-semibold text-decoration-none">
                                {{ $task->title }}
                            </a>
                            @if($task->created_by !== $task->assigned_to && $task->assigned_to === auth()->id())
                                <span class="badge bg-info bg-opacity-10 text-info ms-1">Assigned to you</span>
                            @endif
                        </td>
                        <td>{{ $task->category->name ?? '—' }}</td>
                        @if($userRole !== 'technician')
                        <td>{{ $task->assignee->name ?? '—' }}</td>
                        @endif
                        <td>
                            @if($task->due_date)
                                <span class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-danger fw-semibold' : '' }}">
                                    {{ $task->due_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($task->reminder_date)
                                <i class="bi bi-bell-fill text-warning me-1" title="Reminder set"></i>
                                {{ $task->reminder_date->format('d M Y, h:i A') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @switch($task->status)
                                @case('pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    @break
                                @case('in_progress')
                                    <span class="badge bg-primary">In Progress</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success">Completed</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $task->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($task->status !== 'completed')
                                <form action="{{ route('tasks.complete', $task) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="Mark Complete">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                      onsubmit="return confirm('Delete this task?')" class="d-inline">
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

{{-- Mobile Card View --}}
<div class="d-md-none">
    @forelse($tasks as $task)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1" style="min-width:0;">
                    <a href="{{ route('tasks.show', $task) }}" class="fw-bold text-decoration-none text-dark d-block text-truncate">
                        {{ $task->title }}
                    </a>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @if($task->category)
                            <span class="badge bg-light text-dark border">{{ $task->category->name }}</span>
                        @endif
                        @if($task->created_by !== $task->assigned_to && $task->assigned_to === auth()->id())
                            <span class="badge bg-info bg-opacity-10 text-info">Assigned to you</span>
                        @endif
                    </div>
                </div>
                <div class="ms-2 flex-shrink-0">
                    @switch($task->status)
                        @case('pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                            @break
                        @case('in_progress')
                            <span class="badge bg-primary">In Progress</span>
                            @break
                        @case('completed')
                            <span class="badge bg-success">Completed</span>
                            @break
                    @endswitch
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mb-2 small text-muted">
                @if($userRole !== 'technician' && $task->assignee)
                    <span><i class="bi bi-person me-1"></i>{{ $task->assignee->name }}</span>
                @endif
                @if($task->due_date)
                    <span class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-danger fw-semibold' : '' }}">
                        <i class="bi bi-calendar-event me-1"></i>{{ $task->due_date->format('d M Y') }}
                    </span>
                @endif
                @if($task->reminder_date)
                    <span><i class="bi bi-bell-fill text-warning me-1"></i>{{ $task->reminder_date->format('d M, h:i A') }}</span>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1">
                <div class="small text-muted">
                    <i class="bi bi-clock me-1"></i>{{ $task->created_at->format('d M Y') }}
                </div>
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                    @if($task->status !== 'completed')
                    <form action="{{ route('tasks.complete', $task) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-check-lg"></i></button>
                    </form>
                    @endif
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                          onsubmit="return confirm('Delete this task?')" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-list-task fs-1 d-block mb-2"></i>
            No tasks found. <a href="{{ route('tasks.create') }}">Create your first task</a>.
        </div>
    </div>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($(window).width() >= 768) {
            $('#tasksTable').DataTable({
                paging: true,
                pageLength: 25,
                order: [[{{ $userRole !== 'technician' ? 7 : 6 }}, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [{{ $userRole !== 'technician' ? 8 : 7 }}] }
                ],
                language: {
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-list-task fs-1 d-block mb-2"></i>No tasks found. <a href="{{ route('tasks.create') }}">Create your first task</a>.</div>'
                }
            });
        }
    });
</script>
@endsection
