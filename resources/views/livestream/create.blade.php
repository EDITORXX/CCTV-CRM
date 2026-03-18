@extends('layouts.app')

@section('title', 'Start CCTV View')

@section('styles')
<style>
    .preview-container {
        background: #000;
        border-radius: .75rem;
        overflow: hidden;
        position: relative;
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .preview-container video {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .preview-placeholder {
        color: rgba(255,255,255,.4);
        text-align: center;
    }
    .preview-placeholder i {
        font-size: 3rem;
    }
    .device-status {
        font-size: .85rem;
        padding: .5rem .75rem;
        border-radius: .5rem;
        margin-bottom: 1rem;
    }
    .device-selects { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
    .device-selects label { white-space: nowrap; }
    @media (max-width: 767px) {
        .device-selects { flex-direction: column; align-items: stretch; width: 100%; }
        .device-selects select { width: 100% !important; min-width: 0 !important; }
        .preview-container { aspect-ratio: 4/3; }
    }
    .preview-container:-webkit-full-screen,
    .preview-container:fullscreen {
        width: 100vw; height: 100vh; aspect-ratio: auto; background: #000;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Start CCTV View</h4>
            <a href="{{ route('livestream.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div id="device-status" class="device-status bg-light text-muted d-none">
            <i class="bi bi-usb-plug me-1"></i> Checking for video devices...
        </div>

        {{-- Permission banner (shown when camera blocked/not granted) --}}
        <div id="permission-banner" class="alert alert-warning d-none mb-3" role="alert">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="flex-grow-1">
                    <strong><i class="bi bi-camera-video-off me-1"></i> Camera Permission Required</strong>
                    <div class="small mt-1">USB/external camera dikhane ke liye browser ko camera access dena hoga. Neeche button click karein aur <strong>Allow</strong> karein.</div>
                </div>
                <button type="button" id="grant-permission-btn" class="btn btn-warning btn-sm flex-shrink-0">
                    <i class="bi bi-shield-check me-1"></i> Allow Camera Access
                </button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span><i class="bi bi-camera-video me-1"></i> Camera Preview</span>
                </div>
                <div class="device-selects">
                    <label class="small text-muted mb-0">Main:</label>
                    <select id="device-select" class="form-select form-select-sm" style="width:auto;min-width:160px;" disabled>
                        <option>Loading...</option>
                    </select>
                    <label class="small text-muted mb-0">Second:</label>
                    <select id="device-select-2" class="form-select form-select-sm" style="width:auto;min-width:160px;" disabled>
                        <option value="">None</option>
                    </select>
                    <button type="button" id="refresh-devices-btn" class="btn btn-outline-secondary btn-sm" title="Refresh / Re-scan cameras">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                </div>
                <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>USB camera connect karein → <strong>Allow Camera Access</strong> ya Refresh dabayein.</small>
            </div>
            <div class="card-body p-0">
                <div class="preview-container position-relative" id="preview-wrap">
                    <video id="preview-video" autoplay muted playsinline></video>
                    <video id="preview-video-2" autoplay muted playsinline class="d-none" style="position:absolute;top:0.5rem;right:0.5rem;width:25%;max-width:200px;border:2px solid #fff;border-radius:8px;object-fit:cover;"></video>
                    <div id="preview-placeholder" class="preview-placeholder">
                        <i class="bi bi-camera-video-off"></i>
                        <p class="mt-2 mb-0">Select a video device to preview</p>
                    </div>
                    <button type="button" id="preview-fullscreen-btn" class="btn btn-dark btn-sm d-none" style="position:absolute;bottom:.5rem;right:.5rem;opacity:.7;z-index:5;" title="Fullscreen">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('livestream.store') }}" id="stream-form">
                    @csrf
                    <input type="hidden" name="device_id_1" id="device-id-1" value="">
                    <input type="hidden" name="device_id_2" id="device-id-2" value="">
                    <div class="mb-3">
                        <label for="title" class="form-label">Stream Title <span class="text-muted">(optional)</span></label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Customer site - Sector 21" value="{{ old('title') }}">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Stream Password</label>
                        <div class="input-group" id="password-group">
                            <input type="text" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Set a password for viewers" value="{{ old('password') }}" minlength="4">
                            <button type="button" class="btn btn-outline-secondary" id="gen-pass-btn" title="Generate random password">
                                <i class="bi bi-shuffle"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="no-password-cb">
                            <label class="form-check-label small" for="no-password-cb">No password (open link — anyone with link can view)</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100" id="go-live-btn" disabled>
                        <i class="bi bi-camera-video me-1"></i> Go Live
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var deviceSelect  = document.getElementById('device-select');
    var deviceSelect2 = document.getElementById('device-select-2');
    var previewVideo  = document.getElementById('preview-video');
    var previewVideo2 = document.getElementById('preview-video-2');
    var placeholder   = document.getElementById('preview-placeholder');
    var goLiveBtn     = document.getElementById('go-live-btn');
    var statusEl      = document.getElementById('device-status');
    var genPassBtn    = document.getElementById('gen-pass-btn');
    var currentStream = null;
    var currentStream2 = null;

    var noPassCb = document.getElementById('no-password-cb');
    var passField = document.getElementById('password');
    var passGroup = document.getElementById('password-group');
    noPassCb.addEventListener('change', function() {
        if (this.checked) {
            passField.value = '';
            passField.disabled = true;
            passField.removeAttribute('required');
            passGroup.style.opacity = '0.4';
        } else {
            passField.disabled = false;
            passGroup.style.opacity = '1';
        }
    });

    genPassBtn.addEventListener('click', function() {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        var pass = '';
        for (var i = 0; i < 6; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('password').value = pass;
    });

    var fullscreenBtn = document.getElementById('preview-fullscreen-btn');
    fullscreenBtn.addEventListener('click', function() {
        var el = document.getElementById('preview-wrap');
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        }
    });

    var permissionBanner = document.getElementById('permission-banner');
    var grantPermBtn = document.getElementById('grant-permission-btn');

    // Show/hide permission banner
    function showPermissionBanner(show) {
        if (show) permissionBanner.classList.remove('d-none');
        else permissionBanner.classList.add('d-none');
    }

    // Step 1: Ask permission for ALL cameras including USB
    // Key fix: getUserMedia without deviceId lets browser show ALL cameras
    // After one permission, enumerateDevices returns ALL devices with their IDs
    async function requestPermissionAndEnumerate() {
        var stream = null;
        try {
            // video:true without deviceId = browser shows picker with ALL cameras (including USB)
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        } catch (e) {
            if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
                showPermissionBanner(true);
                return null;
            }
            return null;
        }
        if (stream) {
            stream.getTracks().forEach(function(t) { t.stop(); });
        }
        showPermissionBanner(false);

        // After permission granted, enumerate all devices
        await new Promise(function(r) { setTimeout(r, 400); });
        var devices = await navigator.mediaDevices.enumerateDevices();
        var videoDevices = devices.filter(function(d) { return d.kind === 'videoinput'; });

        // Step 2: For any device that still has no label (USB not yet accessed),
        // request its stream specifically using its deviceId
        for (var i = 0; i < videoDevices.length; i++) {
            var d = videoDevices[i];
            // Only try devices that have a valid deviceId but no label yet
            if (!d.deviceId || d.label) continue;
            try {
                var s = await navigator.mediaDevices.getUserMedia({
                    video: { deviceId: { exact: d.deviceId } },
                    audio: false
                });
                s.getTracks().forEach(function(t) { t.stop(); });
            } catch(e) {}
        }

        // Re-enumerate after accessing individual devices
        await new Promise(function(r) { setTimeout(r, 300); });
        devices = await navigator.mediaDevices.enumerateDevices();
        return devices.filter(function(d) { return d.kind === 'videoinput'; });
    }

    async function loadDevices(keepSelection) {
        var prevMain = keepSelection ? deviceSelect.value : null;
        var prevSecond = keepSelection ? deviceSelect2.value : null;

        var videoDevices = await requestPermissionAndEnumerate();

        if (!videoDevices) {
            // Permission denied - banner already shown
            deviceSelect.innerHTML = '<option>Camera access denied</option>';
            statusEl.className = 'device-status bg-danger bg-opacity-10 text-danger';
            statusEl.innerHTML = '<i class="bi bi-shield-x me-1"></i> Camera access blocked. "Allow Camera Access" button click karein.';
            statusEl.classList.remove('d-none');
            return;
        }

        deviceSelect.innerHTML = '';
        deviceSelect2.innerHTML = '<option value="">None</option>';

        if (videoDevices.length === 0) {
            deviceSelect.innerHTML = '<option>No video device found</option>';
            statusEl.className = 'device-status bg-danger bg-opacity-10 text-danger';
            statusEl.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> No camera detected. USB camera connect karein aur Refresh dabayein.';
            statusEl.classList.remove('d-none');
            return;
        }

        // Check if any device has no label (permission not fully granted for that device)
        var hasUnknown = videoDevices.some(function(d) { return !d.label; });
        if (hasUnknown) showPermissionBanner(true);
        else showPermissionBanner(false);

        videoDevices.forEach(function(d, i) {
            var label = d.label || ('Unknown Camera ' + (i + 1) + ' — Allow permission to identify');
            var opt = document.createElement('option');
            opt.value = d.deviceId;
            opt.textContent = label;
            deviceSelect.appendChild(opt);
            var opt2 = document.createElement('option');
            opt2.value = d.deviceId;
            opt2.textContent = label;
            deviceSelect2.appendChild(opt2);
        });

        deviceSelect.disabled = false;
        deviceSelect2.disabled = false;

        if (prevMain && deviceSelect.querySelector('option[value="' + prevMain + '"]')) {
            deviceSelect.value = prevMain;
        }
        if (prevSecond && deviceSelect2.querySelector('option[value="' + prevSecond + '"]')) {
            deviceSelect2.value = prevSecond;
        }

        var knownCount = videoDevices.filter(function(d) { return !!d.label; }).length;
        statusEl.className = 'device-status bg-success bg-opacity-10 text-success';
        statusEl.innerHTML = '<i class="bi bi-check-circle me-1"></i> ' + videoDevices.length + ' camera(s) found (' + knownCount + ' identified). Select karein aur preview dekhen.';
        statusEl.classList.remove('d-none');

        if (!keepSelection && videoDevices[0].deviceId) {
            startPreview(videoDevices[0].deviceId);
            document.getElementById('device-id-1').value = videoDevices[0].deviceId;
        }
    }

    async function startPreview(deviceId) {
        if (currentStream) {
            currentStream.getTracks().forEach(function(t) { t.stop(); });
            currentStream = null;
        }
        previewVideo.srcObject = null;
        await new Promise(function(r) { setTimeout(r, 200); });

        try {
            // exact: force karta hai sirf yahi camera use ho — koi fallback nahi
            currentStream = await navigator.mediaDevices.getUserMedia({
                video: { deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: true
            });
            previewVideo.srcObject = currentStream;
            placeholder.style.display = 'none';
            previewVideo.style.display = 'block';
            fullscreenBtn.classList.remove('d-none');
            goLiveBtn.disabled = false;
        } catch (err) {
            // Width/height constraint fail ho sakta hai — retry without resolution
            try {
                currentStream = await navigator.mediaDevices.getUserMedia({
                    video: { deviceId: { exact: deviceId } },
                    audio: true
                });
                previewVideo.srcObject = currentStream;
                placeholder.style.display = 'none';
                previewVideo.style.display = 'block';
                fullscreenBtn.classList.remove('d-none');
                goLiveBtn.disabled = false;
            } catch (err2) {
                placeholder.innerHTML = '<i class="bi bi-exclamation-triangle"></i><p class="mt-2 mb-0">Camera access failed: ' + err2.message + '<br><small>Camera kisi aur app mein use ho rahi hai ya permission nahi hai.</small></p>';
                placeholder.style.display = '';
                previewVideo.style.display = 'none';
                fullscreenBtn.classList.add('d-none');
                goLiveBtn.disabled = true;
            }
        }
    }

    async function startPreview2(deviceId) {
        if (currentStream2) { currentStream2.getTracks().forEach(function(t) { t.stop(); }); currentStream2 = null; }
        previewVideo2.srcObject = null;
        if (!deviceId) { previewVideo2.classList.add('d-none'); return; }
        await new Promise(function(r) { setTimeout(r, 200); });
        try {
            currentStream2 = await navigator.mediaDevices.getUserMedia({
                video: { deviceId: { exact: deviceId }, width: { ideal: 640 }, height: { ideal: 360 } },
                audio: false
            });
            previewVideo2.srcObject = currentStream2;
            previewVideo2.classList.remove('d-none');
        } catch (e) {
            try {
                currentStream2 = await navigator.mediaDevices.getUserMedia({
                    video: { deviceId: { exact: deviceId } },
                    audio: false
                });
                previewVideo2.srcObject = currentStream2;
                previewVideo2.classList.remove('d-none');
            } catch(e2) {
                previewVideo2.classList.add('d-none');
            }
        }
    }

    deviceSelect.addEventListener('change', function() {
        if (this.value) startPreview(this.value);
        document.getElementById('device-id-1').value = this.value || '';
    });

    deviceSelect2.addEventListener('change', function() {
        startPreview2(this.value || null);
        document.getElementById('device-id-2').value = this.value || '';
    });

    // Grant permission button — stops current stream then re-requests permission
    grantPermBtn.addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Requesting...';
        // Stop existing streams so browser re-shows the camera picker
        if (currentStream) { currentStream.getTracks().forEach(function(t) { t.stop(); }); currentStream = null; }
        if (currentStream2) { currentStream2.getTracks().forEach(function(t) { t.stop(); }); currentStream2 = null; }
        previewVideo.srcObject = null;
        previewVideo2.srcObject = null;
        loadDevices(false).finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Allow Camera Access';
        });
    });

    document.getElementById('refresh-devices-btn').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat spin-icon"></i>';
        var style = document.createElement('style');
        style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}.spin-icon{animation:spin .6s linear infinite;}';
        document.head.appendChild(style);

        loadDevices(true).finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
            style.remove();
        });
    });

    if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
        loadDevices();

        if (navigator.mediaDevices.ondevicechange !== undefined) {
            navigator.mediaDevices.ondevicechange = function() {
                statusEl.className = 'device-status bg-info bg-opacity-10 text-info';
                statusEl.innerHTML = '<i class="bi bi-usb-plug me-1"></i> New device detected, refreshing...';
                statusEl.classList.remove('d-none');
                loadDevices(true);
            };
        }
    } else {
        statusEl.className = 'device-status bg-danger bg-opacity-10 text-danger';
        statusEl.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Your browser does not support media devices. Use Chrome or Edge.';
        statusEl.classList.remove('d-none');
    }
})();
</script>
@endsection
