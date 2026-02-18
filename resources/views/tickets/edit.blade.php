@extends('layouts.app')

@section('title', 'Edit Ticket')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Ticket</h4>
        <p class="text-muted mb-0">Update ticket {{ $ticket->ticket_number }}</p>
    </div>
    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('tickets.update', $ticket) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="complaint_type" class="form-label">Complaint Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('complaint_type') is-invalid @enderror" id="complaint_type" name="complaint_type" required>
                        <option value="No Video" {{ old('complaint_type', $ticket->complaint_type) === 'No Video' ? 'selected' : '' }}>No Video</option>
                        <option value="HDD Issue" {{ old('complaint_type', $ticket->complaint_type) === 'HDD Issue' ? 'selected' : '' }}>HDD Issue</option>
                        <option value="Camera Dead" {{ old('complaint_type', $ticket->complaint_type) === 'Camera Dead' ? 'selected' : '' }}>Camera Dead</option>
                        <option value="DVR Issue" {{ old('complaint_type', $ticket->complaint_type) === 'DVR Issue' ? 'selected' : '' }}>DVR Issue</option>
                        <option value="Network Issue" {{ old('complaint_type', $ticket->complaint_type) === 'Network Issue' ? 'selected' : '' }}>Network Issue</option>
                        <option value="Other" {{ old('complaint_type', $ticket->complaint_type) === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('complaint_type')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                        <option value="low" {{ old('priority', $ticket->priority) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $ticket->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority', $ticket->priority) === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="open" {{ old('status', $ticket->status) === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ old('status', $ticket->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ old('status', $ticket->status) === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ old('status', $ticket->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                    @error('status')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4" required>{{ old('description', $ticket->description) }}</textarea>
                    @error('description')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Ticket
                </button>
                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
