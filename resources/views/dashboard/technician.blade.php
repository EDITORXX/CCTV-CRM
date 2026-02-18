@extends('layouts.app')

@section('title', 'My Assigned Tickets')

@section('content')

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-headset me-2 text-primary"></i>My Assigned Tickets
        </h6>
        <span class="badge bg-primary rounded-pill">{{ count($assignedTickets ?? []) }} tickets</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="ticketsTable">
                <thead class="table-light">
                    <tr>
                        <th>Ticket #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Site</th>
                        <th>Complaint</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedTickets ?? [] as $ticket)
                    <tr>
                        <td><strong>#{{ $ticket->ticket_number }}</strong></td>
                        <td>{{ $ticket->customer->name ?? '—' }}</td>
                        <td>
                            @if($ticket->customer->phone ?? null)
                                <a href="tel:{{ $ticket->customer->phone }}" class="text-decoration-none">
                                    {{ $ticket->customer->phone }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $ticket->site->name ?? '—' }}</td>
                        <td class="text-truncate" style="max-width: 200px;">{{ $ticket->complaint }}</td>
                        <td>
                            <span class="badge
                                @if($ticket->priority === 'critical') bg-danger
                                @elseif($ticket->priority === 'high') bg-warning text-dark
                                @elseif($ticket->priority === 'medium') bg-info text-dark
                                @else bg-secondary
                                @endif
                            ">{{ ucfirst($ticket->priority) }}</span>
                        </td>
                        <td>
                            <span class="badge
                                @if($ticket->status === 'open') bg-primary
                                @elseif($ticket->status === 'in_progress') bg-warning text-dark
                                @elseif($ticket->status === 'resolved') bg-success
                                @else bg-secondary
                                @endif
                            ">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                @if($ticket->status === 'open')
                                <form method="POST" action="{{ route('tickets.update-status', $ticket->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="btn btn-outline-warning" title="Start Job">
                                        <i class="bi bi-play-fill"></i> Start
                                    </button>
                                </form>
                                @endif

                                @if($ticket->status === 'in_progress')
                                <form method="POST" action="{{ route('tickets.update-status', $ticket->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="resolved">
                                    <button type="submit" class="btn btn-outline-success" title="Mark Complete">
                                        <i class="bi bi-check-circle"></i> Complete
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-outline-secondary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-emoji-smile fs-1 d-block mb-2"></i>
                            <strong>All clear!</strong><br>
                            <span class="small">No tickets assigned to you right now.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#ticketsTable').DataTable({
            pageLength: 25,
            order: [[5, 'desc']],
            language: { search: '', searchPlaceholder: 'Search tickets...' }
        });
    });
</script>
@endsection
