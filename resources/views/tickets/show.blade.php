@extends('layouts.app')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Ticket {{ $ticket->ticket_number }}</h4>
        <p class="text-muted mb-0">{{ $ticket->complaint_type }} — {{ $ticket->customer->name ?? 'Unknown' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Ticket Info --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-1"></i> Ticket Details
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="120">Ticket #</td>
                        <td class="fw-semibold">{{ $ticket->ticket_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Customer</td>
                        <td class="fw-semibold">{{ $ticket->customer->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phone</td>
                        <td>{{ $ticket->customer->phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Site</td>
                        <td>{{ $ticket->site->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Complaint Type</td>
                        <td>{{ $ticket->complaint_type }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Priority</td>
                        <td>
                            @switch($ticket->priority)
                                @case('low')
                                    <span class="badge bg-info">Low</span>
                                    @break
                                @case('medium')
                                    <span class="badge bg-warning text-dark">Medium</span>
                                    @break
                                @case('high')
                                    <span class="badge bg-danger">High</span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @switch($ticket->status)
                                @case('open')
                                    <span class="badge bg-warning text-dark">Open</span>
                                    @break
                                @case('in_progress')
                                    <span class="badge bg-primary">In Progress</span>
                                    @break
                                @case('resolved')
                                    <span class="badge bg-success">Resolved</span>
                                    @break
                                @case('closed')
                                    <span class="badge bg-secondary">Closed</span>
                                    @break
                            @endswitch
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td>{{ $ticket->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @if($ticket->description)
                    <tr>
                        <td class="text-muted">Description</td>
                        <td>{{ $ticket->description }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($ticket->photo)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-camera me-1"></i> Complaint Photo
            </div>
            <div class="card-body text-center">
                <a href="{{ asset('storage/' . $ticket->photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $ticket->photo) }}" alt="Complaint Photo"
                         class="img-fluid rounded" style="max-height: 300px;">
                </a>
                <div class="mt-2">
                    <a href="{{ asset('storage/' . $ticket->photo) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrows-fullscreen me-1"></i> View Full Size
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Assign Technician --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-plus me-1"></i> Assign Technician
            </div>
            <div class="card-body">
                @if($ticket->assignments && $ticket->assignments->count())
                    <div class="mb-3">
                        <strong class="small text-muted">Currently Assigned:</strong>
                        <div class="mt-1">
                            @foreach($ticket->assignments as $assignment)
                                <span class="badge bg-primary me-1 mb-1">
                                    <i class="bi bi-person me-1"></i>{{ $assignment->technician->name ?? '-' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-muted small mb-3">No technician assigned yet.</p>
                @endif

                <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <select class="form-select form-select-sm" name="technician_id" id="technician_id" required>
                        <option value="">— Select Technician —</option>
                        @foreach($technicians ?? [] as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">
                        <i class="bi bi-person-plus me-1"></i> Assign
                    </button>
                </form>
            </div>
        </div>

        {{-- Update Status --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-arrow-repeat me-1"></i> Update Status
            </div>
            <div class="card-body">
                <form action="{{ route('tickets.updateStatus', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="status" class="form-label">New Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="update_notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="update_notes" name="notes" rows="2"
                                      placeholder="Status update notes..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="work_done" class="form-label">Work Done</label>
                            <textarea class="form-control" id="work_done" name="work_done" rows="2"
                                      placeholder="Describe what was done..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i> Update Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clock-history me-1"></i> Timeline
            </div>
            <div class="card-body">
                @if(isset($ticket->updates) && $ticket->updates->count())
                    <div class="timeline">
                        @foreach($ticket->updates->sortByDesc('created_at') as $update)
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 me-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $update->user->name ?? 'System' }}</strong>
                                        @if($update->old_status || $update->new_status)
                                            <span class="text-muted mx-1">changed status</span>
                                            @if($update->old_status)
                                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $update->old_status)) }}</span>
                                            @endif
                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            @if($update->new_status)
                                                <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $update->new_status)) }}</span>
                                            @endif
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $update->created_at->format('d M Y, h:i A') }}</small>
                                </div>
                                @if($update->notes)
                                    <div class="mt-1 text-muted">
                                        <i class="bi bi-chat-left-text me-1"></i>{{ $update->notes }}
                                    </div>
                                @endif
                                @if($update->work_done)
                                    <div class="mt-1">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-wrench me-1"></i>Work: {{ $update->work_done }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                        No updates yet. Use the form to update the ticket status.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
