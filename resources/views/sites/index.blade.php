@extends('layouts.app')

@section('title', 'Sites — ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Sites</h4>
        <p class="text-muted mb-0">
            Customer: <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none">{{ $customer->name }}</a>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.sites.create', $customer) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Site
        </a>
        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Customer
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="sitesTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Site Name</th>
                        <th>Address</th>
                        <th>Contact Person</th>
                        <th>Contact Phone</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sites as $site)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $site->name }}</td>
                        <td>{{ Str::limit($site->address, 50) }}</td>
                        <td>{{ $site->contact_person ?? '—' }}</td>
                        <td>{{ $site->contact_phone ?? '—' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.sites.edit', [$customer, $site]) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('customers.sites.destroy', [$customer, $site]) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this site?')" class="d-inline">
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#sitesTable').DataTable({
            paging: false,
            info: false,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-geo-alt fs-1 d-block mb-2"></i>No sites found. <a href="{{ route('customers.sites.create', $customer) }}">Add your first site</a>.</div>'
            }
        });
    });
</script>
@endsection
