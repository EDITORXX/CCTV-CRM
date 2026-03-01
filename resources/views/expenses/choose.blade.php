@extends('layouts.app')

@section('title', 'Record Expense')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Record Expense</h4>
        <p class="text-muted mb-0">Choose the type of expense to record</p>
    </div>
    @php $pivot = auth()->user()->companies()->where('companies.id', session('current_company_id'))->first()?->pivot; @endphp
    @if($pivot && in_array($pivot->role, ['company_admin', 'manager', 'accountant']))
    <a href="{{ route('site-expenses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Site Expenses
    </a>
    @else
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
    @endif
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <div class="rounded-3 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary mb-3" style="width:64px;height:64px;font-size:2rem;">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <h5 class="card-title">Site Expense</h5>
                <p class="card-text text-muted">Expense linked to a customer site (e.g. on-site work, travel to site).</p>
                <a href="{{ route('site-expenses.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Record Site Expense
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <div class="rounded-3 d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success mb-3" style="width:64px;height:64px;font-size:2rem;">
                    <i class="bi bi-building"></i>
                </div>
                <h5 class="card-title">Regular Expense</h5>
                <p class="card-text text-muted">Company daily expense (fuel, salary, toolkit, etc.) — not linked to any site.</p>
                <a href="{{ route('regular-expenses.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i> Record Regular Expense
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
