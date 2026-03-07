@extends('layouts.app')

@section('title', 'Vendors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Vendors</h4>
        <p class="text-muted mb-0">Manage your vendor/supplier database</p>
    </div>
    <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add New Vendor
    </a>
</div>

{{-- Desktop Table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
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
                    @foreach($vendors as $vendor)
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
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $vendors->links() }}
        </div>
    </div>
</div>

{{-- Mobile Card View --}}
<div class="d-md-none">
    @forelse($vendors as $vendor)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1" style="min-width:0;">
                    <a href="{{ route('vendors.show', $vendor) }}" class="fw-bold text-decoration-none text-dark d-block text-truncate">
                        {{ $vendor->name }}
                    </a>
                    @if($vendor->phone)
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $vendor->phone }}</div>
                    @endif
                </div>
                <div class="ms-2 flex-shrink-0">
                    <span class="badge bg-info">{{ $vendor->purchases_count ?? $vendor->purchases->count() }} purchases</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-2">
                @if($vendor->email)
                    <span class="badge bg-light text-dark border text-truncate" style="max-width:200px;"><i class="bi bi-envelope me-1"></i>{{ $vendor->email }}</span>
                @endif
                @if($vendor->gstin)
                    <span class="badge bg-light text-dark border"><i class="bi bi-building me-1"></i>{{ $vendor->gstin }}</span>
                @endif
            </div>
            <div class="d-flex justify-content-end border-top pt-2 mt-1">
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('vendors.show', $vendor) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('vendors.edit', $vendor) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('vendors.destroy', $vendor) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this vendor?')" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-truck fs-1 d-block mb-2"></i>
            No vendors found. <a href="{{ route('vendors.create') }}">Add your first vendor</a>.
        </div>
    </div>
    @endforelse
    @if($vendors->hasPages())
    <div class="d-flex justify-content-end mt-3">
        {{ $vendors->links() }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($(window).width() >= 768) {
            $('#vendorsTable').DataTable({
                paging: false,
                info: false,
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: [6] }
                ],
                language: {
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-truck fs-1 d-block mb-2"></i>No vendors found. <a href="{{ route('vendors.create') }}">Add your first vendor</a>.</div>'
                }
            });
        }
    });
</script>
@endsection
