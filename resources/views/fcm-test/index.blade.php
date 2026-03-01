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

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm border-primary border-2">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-bell me-2"></i>Allow notifications on this device</h5>
                <p class="text-muted small mb-3">Register this browser so it can receive test notifications. Click the button below — the browser will ask for permission.</p>
                <button type="button" class="btn btn-success" id="fcm-allow-btn-test">
                    <i class="bi bi-bell-fill me-1"></i> Allow notifications
                </button>
                <span id="fcm-allow-status" class="ms-2 small text-muted"></span>
            </div>
        </div>
    </div>
</div>

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
                <p class="small text-muted mt-2 mb-0">No devices registered yet. Use <strong>“Allow notifications”</strong> above to register this device, or ask users to enable notifications in the app menu.</p>
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
</div>
@endsection

@push('scripts')
@if(config('services.firebase.vapid_key'))
<script>
(function() {
    var vapidKey = @json(config('services.firebase.vapid_key'));
    var firebaseConfig = {
        apiKey: "AIzaSyCk_AOW1gaki_wlC-Ubh10j92v6mE-XoX4",
        authDomain: "gold-security-695e8.firebaseapp.com",
        projectId: "gold-security-695e8",
        storageBucket: "gold-security-695e8.firebasestorage.app",
        messagingSenderId: "420165823572",
        appId: "1:420165823572:web:4fadb244cb3d69e04751c1"
    };
    var btn = document.getElementById('fcm-allow-btn-test');
    var statusEl = document.getElementById('fcm-allow-status');
    if (!btn) return;
    btn.addEventListener('click', function() {
        btn.disabled = true;
        if (statusEl) statusEl.textContent = 'Requesting permission…';
        if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
        var messaging = firebase.messaging();
        Notification.requestPermission().then(function(permission) {
            if (permission !== 'granted') return Promise.reject(new Error('Permission denied'));
            if (statusEl) statusEl.textContent = 'Registering…';
            return navigator.serviceWorker.register('{{ url("/firebase-messaging-sw.js") }}');
        }).then(function(reg) {
            return messaging.getToken({ vapidKey: vapidKey, serviceWorkerRegistration: reg });
        }).then(function(token) {
            if (statusEl) statusEl.textContent = 'Saving token…';
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            return fetch('{{ route("api.fcm-token.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ token: token })
            });
        }).then(function(r) {
            if (r.ok) {
                if (statusEl) { statusEl.textContent = ''; statusEl.classList.remove('text-muted'); statusEl.classList.add('text-success'); statusEl.textContent = 'Registered. Reload to see count.'; }
                btn.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Notifications enabled';
                btn.classList.remove('btn-success'); btn.classList.add('btn-outline-success');
                window.location.reload();
            } else return Promise.reject(new Error('Save failed'));
        }).catch(function(err) {
            console.warn('FCM enable error:', err);
            btn.disabled = false;
            if (statusEl) { statusEl.textContent = 'Error: ' + (err.message || 'Permission denied'); statusEl.classList.remove('text-muted'); statusEl.classList.add('text-danger'); }
        });
    });
})();
</script>
@endif
@endpush
