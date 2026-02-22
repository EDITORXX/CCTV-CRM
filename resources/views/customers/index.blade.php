@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Customers</h4>
        <p class="text-muted mb-0">Manage your customer database</p>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Customer
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="customersTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>GSTIN</th>
                        <th>Sites</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td>{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="fw-semibold text-decoration-none">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->email ?? '—' }}</td>
                        <td><code>{{ $customer->gstin ?? '—' }}</code></td>
                        <td><span class="badge bg-info">{{ $customer->sites_count ?? $customer->sites->count() }}</span></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this customer?')" class="d-inline">
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
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#customersTable').DataTable({
            paging: false,
            info: false,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-people fs-1 d-block mb-2"></i>No customers found. <a href="{{ route('customers.create') }}">Add your first customer</a>.</div>'
            }
        });
    });
</script>
@endsection
