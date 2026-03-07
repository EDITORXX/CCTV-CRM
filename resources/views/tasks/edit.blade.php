@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Task</h4>
        <p class="text-muted mb-0">Update task details</p>
    </div>
    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-8">
                    <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title', $task->title) }}"
                           placeholder="Enter task title..." required>
                    @error('title')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="task_category_id" class="form-label">Category</label>
                    <div class="input-group">
                        <select class="form-select @error('task_category_id') is-invalid @enderror"
                                id="task_category_id" name="task_category_id">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('task_category_id', $task->task_category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Add New Category">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    @error('task_category_id')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                @if($userRole !== 'technician')
                <div class="col-md-6">
                    <label for="assigned_to" class="form-label">Assign To <span class="text-danger">*</span></label>
                    <select class="form-select @error('assigned_to') is-invalid @enderror"
                            id="assigned_to" name="assigned_to" required>
                        <option value="">— Select Technician —</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('assigned_to', $task->assigned_to) == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }} ({{ $tech->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="col-md-{{ $userRole !== 'technician' ? '3' : '6' }}">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                           id="due_date" name="due_date"
                           value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
                    @error('due_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-{{ $userRole !== 'technician' ? '3' : '6' }}">
                    <label for="reminder_date" class="form-label">Reminder Date & Time</label>
                    <input type="datetime-local" class="form-control @error('reminder_date') is-invalid @enderror"
                           id="reminder_date" name="reminder_date"
                           value="{{ old('reminder_date', $task->reminder_date ? $task->reminder_date->format('Y-m-d\TH:i') : '') }}">
                    <small class="text-muted">Email reminder will be sent at this time</small>
                    @error('reminder_date')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Customer Info Section --}}
                <div class="col-12">
                    <hr class="my-2">
                    <h6 class="text-muted mb-3"><i class="bi bi-person-lines-fill me-1"></i> Customer Info (Optional)</h6>
                </div>

                <div class="col-md-4">
                    <label for="customer_id" class="form-label">Select Existing Customer</label>
                    <div class="input-group">
                        <select class="form-select" id="customer_id" name="customer_id">
                            <option value="">— None —</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}"
                                        data-name="{{ $cust->name }}"
                                        data-phone="{{ $cust->phone }}"
                                        {{ old('customer_id', $task->customer_id) == $cust->id ? 'selected' : '' }}>
                                    {{ $cust->name }} {{ $cust->phone ? '— '.$cust->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#newCustomerModal" title="Create New Customer">
                            <i class="bi bi-person-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="customer_name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="customer_name" name="customer_name"
                           value="{{ old('customer_name', $task->customer_name) }}" placeholder="Customer name">
                </div>

                <div class="col-md-4">
                    <label for="customer_phone" class="form-label">Customer Phone</label>
                    <input type="text" class="form-control" id="customer_phone" name="customer_phone"
                           value="{{ old('customer_phone', $task->customer_phone) }}" placeholder="Customer phone">
                </div>

                <div class="col-12">
                    <hr class="my-2">
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes" name="notes" rows="4"
                              placeholder="Add any notes or details about this task...">{{ old('notes', $task->notes) }}</textarea>
                    @error('notes')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Update Task
                </button>
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('task-categories.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_redirect" value="{{ route('tasks.edit', $task) }}">
                <div class="modal-header">
                    <h5 class="modal-title">Add Task Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="name"
                               placeholder="e.g. Maintenance" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- New Customer Modal --}}
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-1"></i> Create New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nc_name" placeholder="Customer name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" id="nc_phone" placeholder="Phone number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="nc_email" placeholder="Email address">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" id="nc_address" rows="2" placeholder="Address"></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="nc_create_login">
                    <label class="form-check-label" for="nc_create_login">
                        Create login ID for this customer <small class="text-muted">(requires email)</small>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnSaveNewCustomer">
                    <i class="bi bi-check-lg me-1"></i> Create & Select
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const custSelect = document.getElementById('customer_id');
    const custName = document.getElementById('customer_name');
    const custPhone = document.getElementById('customer_phone');

    custSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (this.value) {
            custName.value = opt.dataset.name || '';
            custPhone.value = opt.dataset.phone || '';
        }
    });

    document.getElementById('btnSaveNewCustomer').addEventListener('click', function() {
        const name = document.getElementById('nc_name').value.trim();
        if (!name) {
            alert('Customer name is required.');
            return;
        }

        const phone = document.getElementById('nc_phone').value.trim();
        const email = document.getElementById('nc_email').value.trim();
        const address = document.getElementById('nc_address').value.trim();
        const createLogin = document.getElementById('nc_create_login').checked;

        if (createLogin && !email) {
            alert('Email is required to create a login ID.');
            return;
        }

        let hidden = document.querySelector('input[name="new_customer_name"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden'; hidden.name = 'new_customer_name';
            custSelect.closest('form').appendChild(hidden);
        }
        hidden.value = name;

        ['new_customer_phone', 'new_customer_email', 'new_customer_address', 'create_login'].forEach(n => {
            let el = document.querySelector('input[name="'+n+'"]');
            if (!el) { el = document.createElement('input'); el.type = 'hidden'; el.name = n; custSelect.closest('form').appendChild(el); }
        });
        document.querySelector('input[name="new_customer_phone"]').value = phone;
        document.querySelector('input[name="new_customer_email"]').value = email;
        document.querySelector('input[name="new_customer_address"]').value = address;
        document.querySelector('input[name="create_login"]').value = createLogin ? '1' : '0';

        custSelect.value = '';
        custName.value = name;
        custPhone.value = phone;

        bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();

        document.getElementById('nc_name').value = '';
        document.getElementById('nc_phone').value = '';
        document.getElementById('nc_email').value = '';
        document.getElementById('nc_address').value = '';
        document.getElementById('nc_create_login').checked = false;
    });
});
</script>
@endsection
