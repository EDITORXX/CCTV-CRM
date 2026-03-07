@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Users</h4>
        <p class="text-muted mb-0">Manage user accounts and roles</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i><span class="d-none d-sm-inline"> Add New</span> User
    </a>
</div>

@if(session('created_user_email'))
<div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
    <div class="d-flex align-items-start gap-3">
        <div class="flex-shrink-0 d-none d-sm-block">
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

{{-- Desktop table --}}
<div class="card border-0 shadow-sm d-none d-md-block">
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
                        <th width="160">Actions</th>
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
                                <button type="button" class="btn btn-outline-warning btn-password-manage" title="Password"
                                        data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                                    <i class="bi bi-key"></i>
                                </button>
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

{{-- Mobile card view --}}
<div class="d-md-none">
    @forelse($users as $user)
    @php
        $roleColors = ['company_admin' => 'danger', 'manager' => 'warning', 'accountant' => 'info', 'technician' => 'primary', 'customer' => 'secondary'];
        $role = $user->pivot->role ?? 'customer';
    @endphp
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="min-w-0">
                    <div class="fw-bold">{{ $user->name }}</div>
                    <small class="text-muted text-break">{{ $user->email }}</small>
                </div>
                <span class="badge bg-{{ $roleColors[$role] ?? 'secondary' }} ms-2 flex-shrink-0">
                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                </span>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-2">
                @if($user->phone)
                    <span class="badge bg-light text-dark border" style="font-size:.7rem;">
                        <i class="bi bi-phone me-1"></i>{{ $user->phone }}
                    </span>
                @endif
                @if($user->is_active)
                    <span class="badge bg-success" style="font-size:.7rem;">Active</span>
                @else
                    <span class="badge bg-danger" style="font-size:.7rem;">Inactive</span>
                @endif
            </div>
            <div class="d-flex justify-content-end">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-warning btn-sm btn-password-manage"
                            data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}">
                        <i class="bi bi-key"></i>
                    </button>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                    @if(auth()->id() !== $user->id)
                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                          onsubmit="return confirm('Delete this user?')" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-5 text-muted">
        <i class="bi bi-person-gear fs-1 d-block mb-2"></i>
        <p>No users found. <a href="{{ route('users.create') }}">Add your first user</a>.</p>
    </div>
    @endforelse
    <div class="d-flex justify-content-end mt-2">
        {{ $users->links() }}
    </div>
</div>

{{-- Password Management Modal --}}
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title" id="passwordModalLabel">
                    <i class="bi bi-key-fill text-warning me-2"></i> Password Management
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="fw-semibold" id="pwModalUserName"></div>
                    <small class="text-muted" id="pwModalUserEmail"></small>
                </div>

                <div id="pwLoading" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="ms-2 text-muted">Loading...</span>
                </div>

                <div id="pwContent" class="d-none">
                    <label class="form-label small text-muted mb-1">Current Password</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="pwCurrentPassword" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="pwToggleVisibility" title="Show/Hide">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary" type="button" id="pwCopyPassword" title="Copy">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>

                    <div id="pwNoPassword" class="alert alert-info small d-none">
                        <i class="bi bi-info-circle me-1"></i> No stored password found. Use "Reset Password" to generate one.
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-warning btn-sm" id="pwResetBtn">
                            <i class="bi bi-arrow-clockwise me-1"></i> Reset Password
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="pwSendEmailBtn">
                            <i class="bi bi-envelope me-1"></i> Send on Email
                        </button>
                    </div>

                    <div id="pwResetResult" class="mt-3 d-none">
                        <div class="alert alert-success small mb-0">
                            <i class="bi bi-check-circle me-1"></i> New password generated:
                            <div class="input-group input-group-sm mt-2">
                                <input type="text" class="form-control font-monospace" id="pwNewPassword" readonly>
                                <button class="btn btn-outline-success" type="button" id="pwCopyNewPassword" title="Copy new password">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="pwEmailResult" class="mt-3 d-none"></div>
                </div>
            </div>
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

        var currentUserId = null;

        $(document).on('click', '.btn-password-manage', function() {
            currentUserId = $(this).data('user-id');
            $('#pwModalUserName').text($(this).data('user-name'));
            $('#pwModalUserEmail').text($(this).data('user-email'));
            $('#pwLoading').show();
            $('#pwContent').addClass('d-none');
            $('#pwResetResult').addClass('d-none');
            $('#pwEmailResult').addClass('d-none');
            $('#pwNoPassword').addClass('d-none');
            $('#pwCurrentPassword').attr('type', 'password');
            $('#pwToggleVisibility i').attr('class', 'bi bi-eye');
            new bootstrap.Modal('#passwordModal').show();

            $.ajax({
                url: '/users/' + currentUserId + '/password-info',
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(data) {
                    $('#pwLoading').hide();
                    $('#pwContent').removeClass('d-none');
                    if (data.has_password && data.password) {
                        $('#pwCurrentPassword').val(data.password);
                        $('#pwNoPassword').addClass('d-none');
                    } else {
                        $('#pwCurrentPassword').val('');
                        $('#pwNoPassword').removeClass('d-none');
                    }
                },
                error: function() {
                    $('#pwLoading').hide();
                    $('#pwContent').removeClass('d-none');
                    $('#pwCurrentPassword').val('');
                    $('#pwNoPassword').removeClass('d-none').text('Could not load password info.');
                }
            });
        });

        $('#pwToggleVisibility').on('click', function() {
            var input = $('#pwCurrentPassword');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).find('i').attr('class', 'bi bi-eye-slash');
            } else {
                input.attr('type', 'password');
                $(this).find('i').attr('class', 'bi bi-eye');
            }
        });

        $('#pwCopyPassword').on('click', function() {
            var pw = $('#pwCurrentPassword').val();
            if (!pw) return;
            navigator.clipboard.writeText(pw).then(function() {
                $('#pwCopyPassword').html('<i class="bi bi-check"></i>');
                setTimeout(function() { $('#pwCopyPassword').html('<i class="bi bi-clipboard"></i>'); }, 1500);
            });
        });

        $('#pwCopyNewPassword').on('click', function() {
            var pw = $('#pwNewPassword').val();
            if (!pw) return;
            navigator.clipboard.writeText(pw).then(function() {
                $('#pwCopyNewPassword').html('<i class="bi bi-check"></i>');
                setTimeout(function() { $('#pwCopyNewPassword').html('<i class="bi bi-clipboard"></i>'); }, 1500);
            });
        });

        $('#pwResetBtn').on('click', function() {
            if (!currentUserId) return;
            if (!confirm('Generate a new password for this user? The old password will be replaced.')) return;
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Resetting...');
            $.ajax({
                url: '/users/' + currentUserId + '/reset-password',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(data) {
                    btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise me-1"></i> Reset Password');
                    if (data.success) {
                        $('#pwCurrentPassword').val(data.password);
                        $('#pwNoPassword').addClass('d-none');
                        $('#pwNewPassword').val(data.password);
                        $('#pwResetResult').removeClass('d-none');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise me-1"></i> Reset Password');
                    alert('Failed to reset password. Please try again.');
                }
            });
        });

        $('#pwSendEmailBtn').on('click', function() {
            if (!currentUserId) return;
            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Sending...');
            $('#pwEmailResult').addClass('d-none');
            $.ajax({
                url: '/users/' + currentUserId + '/send-credentials',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(data) {
                    btn.prop('disabled', false).html('<i class="bi bi-envelope me-1"></i> Send on Email');
                    $('#pwEmailResult').removeClass('d-none').html(
                        '<div class="alert alert-success small mb-0"><i class="bi bi-check-circle me-1"></i> ' + (data.message || 'Credentials sent successfully!') + '</div>'
                    );
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-envelope me-1"></i> Send on Email');
                    var msg = 'Failed to send email.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    $('#pwEmailResult').removeClass('d-none').html(
                        '<div class="alert alert-danger small mb-0"><i class="bi bi-exclamation-circle me-1"></i> ' + msg + '</div>'
                    );
                }
            });
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
