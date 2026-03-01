@extends('layouts.app')

@section('title', 'FCM Test')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">FCM Test – Send to all users</h4>
        <p class="text-muted mb-0">One-click send push notification to every registered device.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

@if($configError)
<div class="alert alert-danger mb-4">
    <strong><i class="bi bi-exclamation-triangle me-1"></i> Configuration problem</strong>
    <p class="mb-0 mt-2">{{ $configError }}</p>
    <p class="mb-0 mt-2 small">See <code>docs/FCM_SETUP.md</code> and ensure <code>FIREBASE_CREDENTIALS</code> points to a valid service account JSON on the server.</p>
</div>
@endif

@if(session('fcm_result'))
@php $r = session('fcm_result'); @endphp
<div class="alert {{ ($r['failure'] ?? 0) > 0 ? 'alert-warning' : (($r['success'] ?? 0) > 0 ? 'alert-success' : 'alert-danger') }} mb-4">
    <strong>
        @if(($r['success'] ?? 0) > 0 && ($r['failure'] ?? 0) == 0)
            <i class="bi bi-check-circle me-1"></i> All sent
        @elseif(($r['success'] ?? 0) > 0 && ($r['failure'] ?? 0) > 0)
            <i class="bi bi-exclamation-circle me-1"></i> Partially sent
        @else
            <i class="bi bi-x-circle me-1"></i> Send failed
        @endif
    </strong>
    <p class="mb-1">Success: <strong>{{ $r['success'] ?? 0 }}</strong> &nbsp;|&nbsp; Failure: <strong>{{ $r['failure'] ?? 0 }}</strong></p>
    @if(!empty($r['errors']))
    <p class="mb-1"><strong>Errors:</strong></p>
    <ul class="mb-0 small">
        @foreach(array_slice($r['errors'], 0, 10) as $err)
        <li>{{ $err }}</li>
        @endforeach
        @if(count($r['errors']) > 10)
        <li>… and {{ count($r['errors']) - 10 }} more.</li>
        @endif
    </ul>
    @endif
</div>
@endif

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-send me-1"></i> Send test to all users
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Registered devices: <strong>{{ $tokenCount }}</strong></p>
                <form action="{{ route('fcm-test.send') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title"
                               value="{{ old('title', 'Test from ERP') }}" required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Body <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="2" required maxlength="1000">{{ old('body', 'This is a test notification.') }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary" @if($configError || $tokenCount === 0) disabled @endif>
                        <i class="bi bi-broadcast me-1"></i> Send to all ({{ $tokenCount }})
                    </button>
                </form>
                @if($tokenCount === 0 && !$configError)
                <p class="small text-muted mt-2 mb-0">No devices registered yet. Ask users to click “Enable Notifications” in the app menu.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-list me-1"></i> Registered tokens ({{ $tokens->count() }})
            </div>
            <div class="card-body p-0">
                @if($tokens->isEmpty())
                <p class="text-muted text-center py-4 mb-0">No FCM tokens yet.</p>
                @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Token (preview)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tokens as $t)
                            <tr>
                                <td>{{ $t->user->name ?? '—' }}<br><small class="text-muted">{{ $t->user->email ?? '' }}</small></td>
                                <td><code class="small">{{ Str::limit($t->token, 30) }}</code></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
