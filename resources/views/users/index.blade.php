@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Users</h4>
        <p class="text-muted mb-0">Manage user accounts and roles</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New User
    </a>
</div>

@if(session('created_user_email'))
<div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
    <div class="d-flex align-items-start gap-3">
        <div class="flex-shrink-0">
            <i class="bi bi-check-circle-fill fs-2 text-success"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-3">User created successfully</h5>
            <p class="mb-2">Share these credentials with the user. Save or download them now — the password will not be shown again.</p>
            <div class="bg-white bg-opacity-75 rounded p-3 mb-3">
                <div class="row g-2 small">
                    <div class="col-12 col-md-6">
                        <strong>Name:</strong> <span id="createdUserName">{{ session('created_user_name') }}</span>
                    </div>
                    <div class="col-12 col-md-6">
                        <strong>User ID (Email):</strong> <code id="createdUserEmail" class="user-credential">{{ session('created_user_email') }}</code>
                    </div>
                    <div class="col-12 col-md-6">
                        <strong>Password:</strong> <code id="createdUserPassword" class="user-credential">{{ session('created_user_password') }}</code>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-success btn-sm" id="downloadCredentialsBtn" data-login-url="{{ url('/login') }}">
                <i class="bi bi-download me-1"></i> Download credentials (.txt)
            </button>
        </div>
    </div>
</div>
@endif

@if(session('success') && !session('created_user_email'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="usersTable">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>
                            @php
                                $roleColors = [
                                    'company_admin' => 'danger',
                                    'manager' => 'warning',
                                    'accountant' => 'info',
                                    'technician' => 'primary',
                                    'customer' => 'secondary',
                                ];
                                $role = $user->pivot->role ?? 'customer';
                            @endphp
                            <span class="badge bg-{{ $roleColors[$role] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                            </span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this user?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            paging: false,
            info: false,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ],
            language: {
                emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-person-gear fs-1 d-block mb-2"></i>No users found. <a href="{{ route('users.create') }}">Add your first user</a>.</div>'
            }
        });

        $('#downloadCredentialsBtn').on('click', function() {
            var name = $('#createdUserName').text().trim();
            var email = $('#createdUserEmail').text().trim();
            var password = $('#createdUserPassword').text().trim();
            var loginUrl = $(this).data('login-url') || '/login';
            var content = 'User created successfully\n';
            content += '================================\n\n';
            content += 'Name: ' + name + '\n';
            content += 'User ID (Email): ' + email + '\n';
            content += 'Password: ' + password + '\n\n';
            content += 'Login URL: ' + loginUrl + '\n';
            content += '--------------------------------\n';
            content += 'Save this file securely. Do not share publicly.';
            var blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'user-credentials-' + email.replace(/[^a-z0-9]/gi, '-') + '.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    });
</script>
@endsection
