@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Support Tickets</h4>
        <p class="text-muted mb-0">View and raise support tickets</p>
    </div>
    <div class="d-flex gap-2">
        <a href="tel:+919919944155" class="btn btn-success">
            <i class="bi bi-telephone-fill me-1"></i> Call Support
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
            <i class="bi bi-plus-lg me-1"></i> New Complaint
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ticket #</th>
                        <th>Problem</th>
                        <th>Photo</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td><strong>{{ $ticket->ticket_number }}</strong></td>
                        <td>{{ Str::limit($ticket->description, 50) }}</td>
                        <td>
                            @if($ticket->photo)
                                <a href="{{ asset('storage/' . $ticket->photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $ticket->photo) }}" alt="Photo" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge
                                @if($ticket->priority === 'urgent') bg-danger
                                @elseif($ticket->priority === 'high') bg-warning text-dark
                                @elseif($ticket->priority === 'medium') bg-info
                                @else bg-secondary @endif
                            ">{{ ucfirst($ticket->priority) }}</span>
                        </td>
                        <td>
                            <span class="badge
                                @if($ticket->status === 'open') bg-danger
                                @elseif($ticket->status === 'in_progress') bg-warning text-dark
                                @elseif($ticket->status === 'resolved') bg-success
                                @else bg-secondary @endif
                            ">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                        </td>
                        <td class="text-muted">{{ $ticket->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-headset fs-1 d-block mb-2"></i>
                            No support tickets yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $tickets->links() }}
</div>

{{-- New Complaint Modal --}}
<div class="modal fade" id="newComplaintModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="complaintForm" action="{{ route('portal.complaints.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Raise New Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Problem <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="description" rows="4" required placeholder="Apni problem yahan likhein..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label fw-semibold">Photo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="photo" id="photo" accept="image/*" capture="environment" required>
                        <small class="text-muted">Problem ki photo upload karein (required)</small>
                        <div id="photoPreview" class="mt-2" style="display:none;">
                            <img id="previewImg" src="" alt="Preview" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid #dee2e6;">
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="tel:+919919944155" class="btn btn-outline-success">
                            <i class="bi bi-telephone-fill me-2"></i> Call Support Now
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('photo').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('previewImg').src = ev.target.result;
            document.getElementById('photoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('complaintForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    var submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            var msg = encodeURIComponent(
                'New Complaint:\n' +
                'Customer: ' + (data.customer_name || '') + '\n' +
                'Problem: ' + (data.description || '') + '\n' +
                (data.photo_url ? 'Photo: ' + data.photo_url + '\n' : '') +
                'Ticket: ' + (data.ticket_number || '')
            );
            window.open('https://wa.me/919919944155?text=' + msg, '_blank');
            window.location.href = '{{ route("portal.complaints") }}';
        } else {
            alert(data.message || 'Something went wrong. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send me-1"></i> Submit';
        }
    })
    .catch(function() {
        alert('Network error. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-send me-1"></i> Submit';
    });
});
</script>
@endsection
