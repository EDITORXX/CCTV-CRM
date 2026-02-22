@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">My Profile</h4>
        <p class="text-muted mb-0">View and update your account information</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person me-1"></i> Personal Information
            </div>
            <div class="card-body">
                <form action="{{ route('portal.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label"><i class="bi bi-telephone me-1"></i>Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
                            @error('phone')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="whatsapp" class="form-label"><i class="bi bi-whatsapp me-1 text-success"></i>WhatsApp Number</label>
                            <input type="text" class="form-control @error('whatsapp') is-invalid @enderror"
                                   id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $customer->whatsapp) }}" placeholder="e.g. 9919944155">
                            @error('whatsapp')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $customer->email) }}">
                            @error('email')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-geo-alt me-1"></i> My Sites
            </div>
            <div class="list-group list-group-flush">
                @forelse($customer->sites as $site)
                <div class="list-group-item">
                    <strong>{{ $site->site_name }}</strong>
                    @if($site->address)
                        <small class="text-muted d-block">{{ $site->address }}</small>
                    @endif
                    @if($site->contact_person)
                        <small class="text-muted d-block">Contact: {{ $site->contact_person }} {{ $site->contact_phone ? '- ' . $site->contact_phone : '' }}</small>
                    @endif
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-4">
                    No sites registered yet.
                </div>
                @endforelse
            </div>
        </div>

        @if($customer->gstin)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-building me-1"></i> Business Details
            </div>
            <div class="card-body">
                <small class="text-muted d-block">GSTIN</small>
                <strong>{{ $customer->gstin }}</strong>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
