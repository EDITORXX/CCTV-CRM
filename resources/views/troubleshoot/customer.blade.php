@extends('layouts.app')

@section('title', 'Troubleshoot')

@section('styles')
<style>
    .preview-wrap {
        background: #000;
        border-radius: .75rem;
        aspect-ratio: 16/9;
        position: relative;
        overflow: hidden;
    }
    .preview-wrap video { width: 100%; height: 100%; object-fit: contain; }
    .preview-pip {
        position: absolute; top: 0.5rem; right: 0.5rem;
        width: 25%; max-width: 200px; border: 2px solid #fff; border-radius: 8px;
        object-fit: cover; z-index: 3;
    }
    .code-display {
        font-size: 1.5rem; font-weight: 700; letter-spacing: .2em;
        background: #1a1c2e; color: #fff; padding: 1rem 1.5rem;
        border-radius: .75rem; text-align: center;
    }
    .code-display small { font-size: .75rem; letter-spacing: normal; opacity: .8; }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h4 class="mb-4"><i class="bi bi-tools me-1"></i> Troubleshoot</h4>

        <div id="start-section" class="card mb-4">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-3">Let the technician see your camera to help fix the issue. You'll get a short code to share.</p>
                <button type="button" class="btn btn-primary btn-lg" id="start-btn">
                    <i class="bi bi-camera-video me-1"></i> Start Troubleshoot
                </button>
            </div>
        </div>

        <div id="active-section" class="d-none">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Give this to technician</div>
                <div class="card-body text-center">
                    <div class="code-display mb-2">
                        <span id="display-code">——</span> <span class="text-white-50">/</span> <span id="display-pin">——</span>
                    </div>
                    <small class="text-muted">Code / PIN — technician enters this to view your camera</small>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body p-0">
                    <div class="preview-wrap" id="video-rotate-wrap" data-rotation="0">
                        <video id="local-video" autoplay muted playsinline></video>
                        <video id="local-video-pip" autoplay muted playsinline class="preview-pip d-none"></video>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary" id="copy-code-btn"><i class="bi bi-clipboard me-1"></i> Copy Code & PIN</button>
                <button type="button" class="btn btn-danger" id="end-btn"><i class="bi bi-stop-circle me-1"></i> End Troubleshoot</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var startBtn = document.getElementById('start-btn');
    var startSection = document.getElementById('start-section');
    var activeSection = document.getElementById('active-section');
    var displayCode = document.getElementById('display-code');
    var displayPin = document.getElementById('display-pin');
    var localVideo = document.getElementById('local-video');
    var localVideoPip = document.getElementById('local-video-pip');
    var endBtn = document.getElementById('end-btn');
    var copyCodeBtn = document.getElementById('copy-code-btn');
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var sessionId = null;
    var shortCode = '';
    var plainPin = '';
    var localStream = null;
    var localStream2 = null;
    var pc = null;
    var pollTimer = null;
    var ICE_SERVERS = [{ urls: 'stun:stun.l.google.com:19302' }, { urls: 'stun:stun1.l.google.com:19302' }];

    startBtn.addEventListener('click', async function() {
        startBtn.disabled = true;
        try {
            var devices = await navigator.mediaDevices.enumerateDevices();
            var videoDevices = devices.filter(function(d) { return d.kind === 'videoinput'; });
            if (videoDevices.length === 0) {
                alert('No camera found.');
                startBtn.disabled = false;
                return;
            }
            localStream = await navigator.mediaDevices.getUserMedia({
                video: { deviceId: videoDevices[0].deviceId ? { exact: videoDevices[0].deviceId } : true, width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: true
            });
            localVideo.srcObject = localStream;
            if (videoDevices.length >= 2) {
                try {
                    localStream2 = await navigator.mediaDevices.getUserMedia({
                        video: { deviceId: { exact: videoDevices[1].deviceId }, width: { ideal: 640 }, height: { ideal: 360 } },
                        audio: false
                    });
                    localVideoPip.srcObject = localStream2;
                    localVideoPip.classList.remove('d-none');
                } catch (e) {}
            }
            var res = await fetch('{{ route("portal.troubleshoot.start") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
            var data = await res.json();
            sessionId = data.session_id;
            shortCode = data.short_code;
            plainPin = data.password;
            displayCode.textContent = shortCode;
            displayPin.textContent = plainPin;
            startSection.classList.add('d-none');
            activeSection.classList.remove('d-none');
            startPolling();
        } catch (err) {
            console.error(err);
            alert('Could not start. Please allow camera access.');
            startBtn.disabled = false;
        }
    });

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(pollSignals, 1500);
        pollSignals();
    }

    async function pollSignals() {
        if (!sessionId) return;
        try {
            var res = await fetch('/portal/troubleshoot/' + sessionId + '/signals', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            var data = await res.json();
            if (!data.active) return;
            data.signals.forEach(handleSignal);
        } catch (e) {}
    }

    async function handleSignal(signal) {
        var type = signal.type;
        var payload = JSON.parse(signal.payload);
        if (type === 'offer') {
            if (pc) try { pc.close(); } catch(e) {}
            pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });
            pc.onicecandidate = function(e) {
                if (e.candidate) sendSignal('technician', 'ice-candidate', JSON.stringify(e.candidate));
            };
            if (localStream) localStream.getTracks().forEach(function(t) { pc.addTrack(t, localStream); });
            if (localStream2) localStream2.getTracks().forEach(function(t) { pc.addTrack(t, localStream2); });
            await pc.setRemoteDescription(new RTCSessionDescription(payload));
            var answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await sendSignal('technician', 'answer', JSON.stringify(answer));
        } else if (type === 'ice-candidate' && pc) {
            try { await pc.addIceCandidate(new RTCIceCandidate(payload)); } catch(e) {}
        }
    }

    function sendSignal(toPeer, type, payload) {
        return fetch('/portal/troubleshoot/' + sessionId + '/signal', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ to_peer: toPeer, type: type, payload: payload })
        });
    }

    copyCodeBtn.addEventListener('click', function() {
        var text = 'Code: ' + shortCode + ' / PIN: ' + plainPin;
        navigator.clipboard.writeText(text).then(function() {
            copyCodeBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
            setTimeout(function() { copyCodeBtn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Code & PIN'; }, 2000);
        });
    });

    endBtn.addEventListener('click', async function() {
        if (pollTimer) clearInterval(pollTimer);
        if (pc) try { pc.close(); } catch(e) {}
        if (localStream) localStream.getTracks().forEach(function(t) { t.stop(); });
        if (localStream2) localStream2.getTracks().forEach(function(t) { t.stop(); });
        try {
            await fetch('{{ route("portal.troubleshoot.end") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({})
            });
        } catch (e) {}
        activeSection.classList.add('d-none');
        startSection.classList.remove('d-none');
        startBtn.disabled = false;
        sessionId = null;
    });

    window.addEventListener('beforeunload', function() {
        if (pollTimer) clearInterval(pollTimer);
        if (pc) try { pc.close(); } catch(e) {}
    });
})();
</script>
@endsection
