@extends('layouts.app')

@section('title', 'My Assigned Tickets')

@section('content')

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-headset me-2 text-primary"></i>My Assigned Tickets
        </h6>
        <span class="badge bg-primary rounded-pill">{{ count($assignedTickets ?? []) }} tickets</span>
    </div>
    <div class="card-body">
        @forelse($assignedTickets ?? [] as $ticket)
        <div class="border rounded-3 p-3 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="fw-bold">#{{ $ticket->ticket_number }}</span>
                <div class="d-flex gap-1 flex-wrap justify-content-end">
                    <span class="badge
                        @if($ticket->priority === 'critical') bg-danger
                        @elseif($ticket->priority === 'high') bg-warning text-dark
                        @elseif($ticket->priority === 'medium') bg-info text-dark
                        @else bg-secondary
                        @endif
                    ">{{ ucfirst($ticket->priority) }}</span>
                    <span class="badge
                        @if($ticket->status === 'open') bg-primary
                        @elseif($ticket->status === 'in_progress') bg-warning text-dark
                        @elseif($ticket->status === 'resolved') bg-success
                        @else bg-secondary
                        @endif
                    ">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                </div>
            </div>

            <div class="mb-2">
                <div class="fw-semibold">{{ $ticket->customer->name ?? '—' }}</div>
                @if($ticket->complaint_type)
                    <div class="text-muted small">{{ $ticket->complaint_type }}</div>
                @endif
                @if($ticket->site)
                    <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i>{{ $ticket->site->name ?? '—' }}</div>
                @endif
                <div class="text-muted small"><i class="bi bi-calendar me-1"></i>{{ $ticket->created_at->format('d M Y') }}</div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @if($ticket->customer->phone ?? null)
                <a href="tel:{{ $ticket->customer->phone }}" class="btn btn-sm btn-success">
                    <i class="bi bi-telephone-fill me-1"></i> Call
                </a>
                @endif

                @if($ticket->status === 'open')
                <form method="POST" action="{{ route('tickets.updateStatus', $ticket->id) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="bi bi-play-fill me-1"></i> Start
                    </button>
                </form>
                @endif

                @if($ticket->status === 'in_progress')
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#ticketModal{{ $ticket->id }}" onclick="document.getElementById('completeSection{{ $ticket->id }}').style.display='block'">
                    <i class="bi bi-check-circle me-1"></i> Complete
                </button>
                @endif

                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ticketModal{{ $ticket->id }}">
                    <i class="bi bi-eye me-1"></i> View
                </button>
            </div>
        </div>

        {{-- Detail Modal --}}
        <div class="modal fade" id="ticketModal{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold">
                            <i class="bi bi-ticket-detailed me-1"></i> Ticket #{{ $ticket->ticket_number }}
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-borderless mb-3">
                            <tr>
                                <td class="text-muted" width="110">Customer</td>
                                <td class="fw-semibold">{{ $ticket->customer->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phone</td>
                                <td>
                                    @if($ticket->customer->phone ?? null)
                                        <a href="tel:{{ $ticket->customer->phone }}" class="text-decoration-none">
                                            {{ $ticket->customer->phone }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Site</td>
                                <td>{{ $ticket->site->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Complaint</td>
                                <td>{{ $ticket->complaint_type ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Priority</td>
                                <td>
                                    <span class="badge
                                        @if($ticket->priority === 'critical') bg-danger
                                        @elseif($ticket->priority === 'high') bg-warning text-dark
                                        @elseif($ticket->priority === 'medium') bg-info text-dark
                                        @else bg-secondary
                                        @endif
                                    ">{{ ucfirst($ticket->priority) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status</td>
                                <td>
                                    <span class="badge
                                        @if($ticket->status === 'open') bg-primary
                                        @elseif($ticket->status === 'in_progress') bg-warning text-dark
                                        @elseif($ticket->status === 'resolved') bg-success
                                        @else bg-secondary
                                        @endif
                                    ">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created</td>
                                <td>{{ $ticket->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        </table>

                        @if($ticket->description)
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small mb-1">Description</label>
                            <div class="border rounded-3 p-2 bg-light small">{{ $ticket->description }}</div>
                        </div>
                        @endif

                        @if($ticket->photo)
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small mb-1">Complaint Photo</label>
                            <div class="text-center">
                                <a href="{{ asset('storage/' . $ticket->photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $ticket->photo) }}" alt="Complaint Photo"
                                         class="img-fluid rounded border" style="max-height: 250px;">
                                </a>
                            </div>
                        </div>
                        @endif

                        @if($ticket->status === 'in_progress')
                        <div id="completeSection{{ $ticket->id }}" class="border-top pt-3 mt-2" style="display:none;">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-check-circle me-1 text-success"></i> Mark as Complete</h6>
                            <form method="POST" action="{{ route('tickets.updateStatus', $ticket->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Notes</label>
                                    <textarea class="form-control form-control-sm" name="notes" rows="2" placeholder="Status update notes..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Work Done</label>
                                    <textarea class="form-control form-control-sm" name="work_done" rows="2" placeholder="Describe what was done..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-lg me-1"></i> Mark as Resolved
                                </button>
                            </form>
                        </div>

                        <div id="completeToggle{{ $ticket->id }}" class="border-top pt-3 mt-2">
                            <button type="button" class="btn btn-outline-success w-100" onclick="document.getElementById('completeSection{{ $ticket->id }}').style.display='block'; this.parentElement.style.display='none';">
                                <i class="bi bi-check-circle me-1"></i> Mark as Complete
                            </button>
                        </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($ticket->customer->phone ?? null)
                        <a href="tel:{{ $ticket->customer->phone }}" class="btn btn-success me-auto">
                            <i class="bi bi-telephone-fill me-1"></i> Call Customer
                        </a>
                        @endif
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
            <strong>All clear!</strong>
            <div class="small">No tickets assigned to you right now.</div>
        </div>
        @endforelse
    </div>
</div>

@endsection
