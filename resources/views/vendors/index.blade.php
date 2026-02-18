@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Vendors</h4>
        <p class="text-muted mb-0">Manage your vendor/supplier database</p>
    </div>
    <a href="{{ route('vendors.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Vendor
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="vendorsTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>GSTIN</th>
                        <th>Purchases</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendors as $vendor)
                    <tr>
                        <td>{{ $loop->iteration + ($vendors->currentPage() - 1) * $vendors->perPage() }}</td>
                        <td>
                            <a href="{{ route('vendors.show', $vendor) }}" class="fw-semibold text-decoration-none">
                                {{ $vendor->name }}
                            </a>
                        </td>
                        <td>{{ $vendor->phone ?? '—' }}</td>
                        <td>{{ $vendor->email ?? '—' }}</td>
                        <td><code>{{ $vendor->gstin ?? '—' }}</code></td>
                        <td><span class="badge bg-info">{{ $vendor->purchases_count ?? $vendor->purchases->count() }}</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('vendors.destroy', $vendor) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this vendor?')" class="d-inline">
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
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-truck fs-1 d-block mb-2"></i>
                            No vendors found. <a href="{{ route('vendors.create') }}">Add your first vendor</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $vendors->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#vendorsTable').DataTable({
            paging: false,
            info: false,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ]
        });
    });
</script>
@endsection
