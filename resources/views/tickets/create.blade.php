@extends('layouts.app')

@section('title', 'New Ticket')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">New Service Ticket</h4>
        <p class="text-muted mb-0">Create a new customer complaint or service request</p>
    </div>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('tickets.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                        <option value="">— Select Customer —</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} — {{ $customer->phone }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="site_id" class="form-label">Site <span class="text-danger">*</span></label>
                    <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id" required>
                        <option value="">— Select Customer First —</option>
                    </select>
                    @error('site_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="ticket_number" class="form-label">Ticket Number</label>
                    <input type="text" class="form-control @error('ticket_number') is-invalid @enderror"
                           id="ticket_number" name="ticket_number"
                           value="{{ old('ticket_number', $nextTicketNumber ?? '') }}" readonly>
                    @error('ticket_number')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="complaint_type" class="form-label">Complaint Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('complaint_type') is-invalid @enderror" id="complaint_type" name="complaint_type" required>
                        <option value="">— Select Type —</option>
                        <option value="No Video" {{ old('complaint_type') === 'No Video' ? 'selected' : '' }}>No Video</option>
                        <option value="HDD Issue" {{ old('complaint_type') === 'HDD Issue' ? 'selected' : '' }}>HDD Issue</option>
                        <option value="Camera Dead" {{ old('complaint_type') === 'Camera Dead' ? 'selected' : '' }}>Camera Dead</option>
                        <option value="DVR Issue" {{ old('complaint_type') === 'DVR Issue' ? 'selected' : '' }}>DVR Issue</option>
                        <option value="Network Issue" {{ old('complaint_type') === 'Network Issue' ? 'selected' : '' }}>Network Issue</option>
                        <option value="Other" {{ old('complaint_type') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('complaint_type')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                        <option value="low" {{ old('priority', 'medium') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4"
                              placeholder="Describe the issue in detail..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Ticket
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#customer_id').on('change', function() {
        var customerId = $(this).val();
        var $siteSelect = $('#site_id');
        $siteSelect.html('<option value="">— Loading Sites... —</option>');

        if (!customerId) {
            $siteSelect.html('<option value="">— Select Customer First —</option>');
            return;
        }

        var url = "{{ route('api.customer.sites', ':id') }}".replace(':id', customerId);
        $.get(url, function(data) {
            var opts = '<option value="">— Select Site —</option>';
            $.each(data, function(i, site) {
                opts += '<option value="' + site.id + '">' + site.name + '</option>';
            });
            $siteSelect.html(opts);
        }).fail(function() {
            $siteSelect.html('<option value="">— Failed to load sites —</option>');
        });
    });

    @if(old('customer_id'))
        $('#customer_id').trigger('change');
    @endif
});
</script>
@endsection
