@extends('layouts.app')

@section('title', 'Task: ' . $task->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $task->title }}</h4>
        <p class="text-muted mb-0">{{ $task->category->name ?? 'Uncategorized' }} — Created by {{ $task->creator->name ?? 'Unknown' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-4">
    {{-- Task Details --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-1"></i> Task Details
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="130">Title</td>
                        <td class="fw-semibold">{{ $task->title }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Category</td>
                        <td>
                            @if($task->category)
                                <span class="badge bg-light text-dark border">{{ $task->category->name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Assigned To</td>
                        <td class="fw-semibold">{{ $task->assignee->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created By</td>
                        <td>{{ $task->creator->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
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
                    </tr>
                    <tr>
                        <td class="text-muted">Due Date</td>
                        <td>
                            @if($task->due_date)
                                <span class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-danger fw-semibold' : '' }}">
                                    {{ $task->due_date->format('d M Y') }}
                                    @if($task->due_date->isPast() && $task->status !== 'completed')
                                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Overdue"></i>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reminder</td>
                        <td>
                            @if($task->reminder_date)
                                <i class="bi bi-bell-fill text-warning me-1"></i>
                                {{ $task->reminder_date->format('d M Y, h:i A') }}
                                @if($task->custom_reminder_sent)
                                    <span class="badge bg-success bg-opacity-10 text-success ms-1">Sent</span>
                                @endif
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td>{{ $task->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @if($task->completed_at)
                    <tr>
                        <td class="text-muted">Completed</td>
                        <td class="text-success">{{ $task->completed_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($task->customer_name || $task->customer_phone)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-badge me-1"></i> Customer Info
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    @if($task->customer_name)
                    <tr>
                        <td class="text-muted" width="130">Name</td>
                        <td class="fw-semibold">
                            {{ $task->customer_name }}
                            @if($task->customer && $task->customer_id)
                                <a href="{{ route('customers.show', $task->customer_id) }}" class="ms-1 small text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right"></i> View
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($task->customer_phone)
                    <tr>
                        <td class="text-muted">Phone</td>
                        <td>
                            <a href="tel:{{ $task->customer_phone }}" class="text-decoration-none">
                                <i class="bi bi-telephone-fill text-success me-1"></i>{{ $task->customer_phone }}
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif

        {{-- Status Actions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-arrow-repeat me-1"></i> Update Status
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    @if($task->status !== 'in_progress')
                    <form action="{{ route('tasks.in-progress', $task) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-fill me-1"></i> Mark In Progress
                        </button>
                    </form>
                    @endif

                    @if($task->status !== 'completed')
                    <form action="{{ route('tasks.complete', $task) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Mark Completed
                        </button>
                    </form>
                    @endif

                    @if($task->status === 'completed')
                    <form action="{{ route('tasks.in-progress', $task) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reopen Task
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-journal-text me-1"></i> Notes
            </div>
            <div class="card-body">
                @if($task->notes)
                    <div class="p-3 bg-light rounded">
                        {!! nl2br(e($task->notes)) !!}
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-journal-text fs-1 d-block mb-2"></i>
                        No notes added. <a href="{{ route('tasks.edit', $task) }}">Edit this task</a> to add notes.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
