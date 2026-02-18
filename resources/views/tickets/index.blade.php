@extends('layouts.app')

@section('title', 'Service Tickets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Service Tickets</h4>
        <p class="text-muted mb-0">Manage customer complaints and service requests</p>
    </div>
    <a href="{{ route('tickets.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Ticket
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="ticketsTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Ticket #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Site</th>
                        <th>Complaint Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold text-decoration-none">
                                {{ $ticket->ticket_number }}
                            </a>
                        </td>
                        <td>{{ $ticket->customer->name ?? '—' }}</td>
                        <td>{{ $ticket->customer->phone ?? '—' }}</td>
                        <td>{{ $ticket->site->name ?? '—' }}</td>
                        <td>{{ $ticket->complaint_type }}</td>
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
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($ticket->priority) }}</span>
                            @endswitch
                        </td>
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
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($ticket->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if($ticket->assignees && $ticket->assignees->count())
                                {{ $ticket->assignees->pluck('name')->join(', ') }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>{{ $ticket->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST"
                                      onsubmit="return confirm('Delete this ticket?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-4 text-muted">
                            <i class="bi bi-headset fs-1 d-block mb-2"></i>
                            No tickets found. <a href="{{ route('tickets.create') }}">Create your first ticket</a>.
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
            paging: true,
            pageLength: 25,
            order: [[9, 'desc']],
            columnDefs: [
                { orderable: false, targets: [10] }
            ]
        });
    });
</script>
@endsection
