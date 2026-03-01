@extends('layouts.app')

@section('title', 'CCTV View — Broadcasting')

@section('styles')
<style>
    .broadcast-container {
        background: #000;
        border-radius: .75rem;
        overflow: hidden;
        aspect-ratio: 16/9;
        position: relative;
    }
    .broadcast-container video {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    .live-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: #dc3545;
        color: #fff;
        padding: .25rem .75rem;
        border-radius: 2rem;
        font-size: .8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: .35rem;
        animation: pulse-live 2s infinite;
        z-index: 5;
    }
    @keyframes pulse-live {
        0%, 100% { opacity: 1; }
        50% { opacity: .6; }
    }
    .viewers-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(0,0,0,.6);
        color: #fff;
        padding: .25rem .75rem;
        border-radius: 2rem;
        font-size: .8rem;
        z-index: 5;
    }
    .share-box {
        background: #f0f4ff;
        border: 1.5px solid #b6ccfe;
        border-radius: .75rem;
        padding: 1rem;
    }
    .share-url {
        font-family: monospace;
        font-size: .85rem;
        word-break: break-all;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        padding: .5rem .75rem;
        user-select: all;
    }
    .stream-controls {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        align-items: center;
        padding: .5rem 0;
    }
    .stream-controls .btn { font-size: .85rem; }
    .recording-dot { width: 8px; height: 8px; background: #dc3545; border-radius: 50%; animation: blink 1s infinite; }
    @keyframes blink { 50% { opacity: .4; } }

    @media (max-width: 576px) {
        .stream-controls .btn span.btn-label { display: none; }
        .stream-controls .btn { padding: .35rem .55rem; font-size: .8rem; }
        .stream-controls > .fw-semibold { display: none; }
        .share-url { font-size: .75rem; }
    }
    @media (max-width: 767px) {
        #device-row .d-flex { flex-direction: column; }
        #device-row select { width: 100% !important; min-width: 0 !important; }
    }
    @media (orientation: landscape) and (max-height: 500px) {
        .stream-controls { padding: .25rem 0; }
        .stream-controls .btn { padding: .25rem .4rem; font-size: .75rem; }
        .broadcast-container { aspect-ratio: auto; height: calc(100vh - 100px); }
    }

    .broadcast-container:-webkit-full-screen,
    .broadcast-container:fullscreen {
        width: 100vw; height: 100vh; aspect-ratio: auto;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="bi bi-camera-video text-danger me-1"></i>
                {{ $stream->title ?: 'CCTV View' }}
            </h4>
            <form method="POST" action="{{ route('livestream.stop', $stream) }}" id="stop-form">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Stop this CCTV stream?')">
                    <i class="bi bi-stop-circle me-1"></i> Stop Stream
                </button>
            </form>
        </div>

        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="broadcast-container" id="video-rotate-wrap" data-rotation="0">
                    <div class="live-badge"><i class="bi bi-circle-fill" style="font-size:.5rem;"></i> LIVE</div>
                    <div class="viewers-badge"><i class="bi bi-people-fill me-1"></i> <span id="viewer-count">0</span> viewers</div>
                    <video id="local-video" autoplay muted playsinline></video>
                    <video id="local-video-2" autoplay muted playsinline class="d-none" style="position:absolute;top:0.5rem;right:0.5rem;width:25%;max-width:200px;border:2px solid #fff;border-radius:8px;object-fit:cover;z-index:3;"></video>
                </div>
            </div>
        </div>

        <div class="stream-controls mb-3">
            <span class="fw-semibold me-2">Controls:</span>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="rotate-btn" title="Rotate Main"><i class="bi bi-arrow-clockwise"></i> <span class="btn-label">Rotate</span></button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="rotate-pip-btn" title="Rotate PiP"><i class="bi bi-arrow-repeat"></i> <span class="btn-label">Rotate PiP</span></button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="record-btn" title="Record"><i class="bi bi-record-circle"></i> <span class="btn-label">Record</span></button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="snap-btn" title="Snapshot"><i class="bi bi-camera"></i> <span class="btn-label">Snap</span></button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="mute-btn" title="Mute audio"><i class="bi bi-mic"></i> <span class="btn-label">Mute</span></button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="fullscreen-btn" title="Fullscreen"><i class="bi bi-arrows-fullscreen"></i> <span class="btn-label">Fullscreen</span></button>
            <span id="record-indicator" class="d-none align-items-center gap-1"><span class="recording-dot"></span><small>REC</small></span>
        </div>

        <div id="device-row" class="mb-3">
            <label class="form-label fw-semibold">Video Source</label>
            <div class="d-flex gap-2 flex-wrap">
                <select id="device-select" class="form-select" style="min-width:180px;">
                    <option>Loading...</option>
                </select>
                <select id="device-select-2" class="form-select" style="min-width:180px;">
                    <option value="">None (single camera)</option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-share me-1"></i> Share CCTV View</div>
            <div class="card-body">
                <div class="share-box mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">CCTV View Link</label>
                    <div class="share-url" id="share-url">{{ $shareUrl }}</div>
                </div>
                @if(session('_live_plain_pass_' . $stream->id))
                <div class="mb-3" id="pass-section">
                    <label class="form-label small fw-semibold text-muted mb-1">Password</label>
                    <div class="share-url" id="share-pass">{{ session('_live_plain_pass_' . $stream->id) }}</div>
                </div>
                <button type="button" class="btn btn-primary w-100 mb-2" id="copy-btn">
                    <i class="bi bi-clipboard me-1"></i> Copy Link & Password
                </button>
                @endif
                <button type="button" class="btn btn-outline-primary w-100 mb-2" id="copy-link-btn">
                    <i class="bi bi-link-45deg me-1"></i> Copy Link Only
                </button>
                <button type="button" class="btn btn-success w-100" id="whatsapp-btn">
                    <i class="bi bi-whatsapp me-1"></i> Share via WhatsApp
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-info-circle me-1"></i> CCTV View Info</div>
            <div class="card-body small">
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                <p class="mb-1"><strong>Started:</strong> {{ $stream->started_at->format('h:i A') }}</p>
                <p class="mb-0"><strong>Stream ID:</strong> #{{ $stream->id }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var STREAM_ID    = {{ $stream->id }};
    var SIGNAL_POST  = '{{ route("livestream.signal", $stream) }}';
    var SIGNAL_GET   = '{{ route("livestream.signals", $stream) }}';
    var CSRF         = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var SHARE_URL    = '{{ $shareUrl }}';
    var DEVICE_ID_1  = @json($deviceId1 ?? null);
    var DEVICE_ID_2  = @json($deviceId2 ?? null);

    var localVideo   = document.getElementById('local-video');
    var localVideo2  = document.getElementById('local-video-2');
    var deviceSelect = document.getElementById('device-select');
    var deviceSelect2 = document.getElementById('device-select-2');
    var viewerCount  = document.getElementById('viewer-count');
    var localStream  = null;
    var localStream2 = null;
    var peers        = {};
    var pollTimer    = null;

    var ICE_SERVERS = [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' }
    ];

    var HAS_PASSWORD = {{ session('_live_plain_pass_' . $stream->id) ? 'true' : 'false' }};

    // ── Copy / Share ──

    if (HAS_PASSWORD && document.getElementById('copy-btn')) {
        document.getElementById('copy-btn').addEventListener('click', function() {
            var pass = document.getElementById('share-pass').textContent;
            var text = 'CCTV View\nLink: ' + SHARE_URL + '\nPassword: ' + pass;
            navigator.clipboard.writeText(text).then(function() {
                var btn = document.getElementById('copy-btn');
                btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
                setTimeout(function() { btn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Link & Password'; }, 2000);
            });
        });
    }

    document.getElementById('copy-link-btn').addEventListener('click', function() {
        navigator.clipboard.writeText(SHARE_URL).then(function() {
            var btn = document.getElementById('copy-link-btn');
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Link Copied!';
            setTimeout(function() { btn.innerHTML = '<i class="bi bi-link-45deg me-1"></i> Copy Link Only'; }, 2000);
        });
    });

    document.getElementById('whatsapp-btn').addEventListener('click', function() {
        var text = 'CCTV View\nLink: ' + SHARE_URL;
        if (HAS_PASSWORD) {
            var pass = document.getElementById('share-pass').textContent;
            text += '\nPassword: ' + pass;
        }
        window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
    });

    // ── Rotation (Main) ──
    var videoWrap = document.getElementById('video-rotate-wrap');
    var rotations = [0, 90, 180, 270];
    var mainRotIdx = 0;
    document.getElementById('rotate-btn').addEventListener('click', function() {
        mainRotIdx = (mainRotIdx + 1) % 4;
        localVideo.style.transform = 'rotate(' + rotations[mainRotIdx] + 'deg)';
    });

    // ── Rotation (PiP) ──
    var pipRotIdx = 0;
    document.getElementById('rotate-pip-btn').addEventListener('click', function() {
        pipRotIdx = (pipRotIdx + 1) % 4;
        localVideo2.style.transform = 'rotate(' + rotations[pipRotIdx] + 'deg)';
    });

    // ── Fullscreen ──
    document.getElementById('fullscreen-btn').addEventListener('click', function() {
        var el = document.getElementById('video-rotate-wrap');
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        }
    });

    // ── Record ──
    var mediaRecorder = null;
    var recordChunks = [];
    document.getElementById('record-btn').addEventListener('click', function() {
        var btn = this;
        var ind = document.getElementById('record-indicator');
        if (!localStream) return;
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            ind.classList.add('d-none');
            btn.innerHTML = '<i class="bi bi-record-circle"></i> <span class="btn-label">Record</span>';
            return;
        }
        recordChunks = [];
        var mime = MediaRecorder.isTypeSupported('video/webm;codecs=vp9') ? 'video/webm;codecs=vp9' : 'video/webm';
        mediaRecorder = new MediaRecorder(localStream, { mimeType: mime });
        mediaRecorder.ondataavailable = function(e) { if (e.data.size) recordChunks.push(e.data); };
        mediaRecorder.onstop = function() {
            var blob = new Blob(recordChunks, { type: 'video/webm' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'cctv-record-' + Date.now() + '.webm';
            a.click();
            URL.revokeObjectURL(a.href);
        };
        mediaRecorder.start();
        ind.classList.remove('d-none');
        ind.style.display = 'flex';
        btn.innerHTML = '<i class="bi bi-stop-circle"></i> <span class="btn-label">Stop</span>';
    });

    // ── Snap ──
    document.getElementById('snap-btn').addEventListener('click', function() {
        var v = document.getElementById('local-video');
        if (!v.videoWidth) return;
        var c = document.createElement('canvas');
        c.width = v.videoWidth;
        c.height = v.videoHeight;
        c.getContext('2d').drawImage(v, 0, 0);
        c.toBlob(function(blob) {
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'cctv-snap-' + Date.now() + '.png';
            a.click();
            URL.revokeObjectURL(a.href);
        }, 'image/png');
    });

    // ── Mute ──
    document.getElementById('mute-btn').addEventListener('click', function() {
        var btn = this;
        if (!localStream) return;
        var audioTracks = localStream.getAudioTracks();
        if (audioTracks.length === 0) return;
        var enabled = !audioTracks[0].enabled;
        audioTracks[0].enabled = enabled;
        btn.innerHTML = enabled ? '<i class="bi bi-mic"></i> <span class="btn-label">Mute</span>' : '<i class="bi bi-mic-mute"></i> <span class="btn-label">Unmute</span>';
        Object.keys(peers).forEach(function(pid) {
            var senders = peers[pid].pc.getSenders();
            senders.forEach(function(s) {
                if (s.track && s.track.kind === 'audio') s.replaceTrack(audioTracks[0]);
            });
        });
    });

    // ── Device listing & preview ──

    async function loadDevices() {
        try { await navigator.mediaDevices.getUserMedia({ video: true }); } catch(e) {}
        var devices = await navigator.mediaDevices.enumerateDevices();
        var videoDevices = devices.filter(function(d) { return d.kind === 'videoinput'; });
        deviceSelect.innerHTML = '';
        deviceSelect2.innerHTML = '<option value="">None (single camera)</option>';
        videoDevices.forEach(function(d, i) {
            var opt = document.createElement('option');
            opt.value = d.deviceId;
            opt.textContent = d.label || ('Camera ' + (i + 1));
            deviceSelect.appendChild(opt);
            var opt2 = document.createElement('option');
            opt2.value = d.deviceId;
            opt2.textContent = d.label || ('Camera ' + (i + 1));
            deviceSelect2.appendChild(opt2);
        });
        var d1 = DEVICE_ID_1 || (videoDevices[0] && videoDevices[0].deviceId);
        var d2 = DEVICE_ID_2 || null;
        if (d1) {
            deviceSelect.value = d1;
            if (d2) deviceSelect2.value = d2;
            await startCapture(d1, d2);
        }
        startPolling();
    }

    async function startCapture(deviceId1, deviceId2) {
        if (localStream) { localStream.getTracks().forEach(function(t) { t.stop(); }); localStream = null; }
        if (localStream2) { localStream2.getTracks().forEach(function(t) { t.stop(); }); localStream2 = null; }
        localVideo2.classList.add('d-none');
        localVideo2.srcObject = null;

        localStream = await navigator.mediaDevices.getUserMedia({
            video: { deviceId: { exact: deviceId1 }, width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: true
        });
        localVideo.srcObject = localStream;

        if (deviceId2 && deviceId2 !== deviceId1) {
            try {
                localStream2 = await navigator.mediaDevices.getUserMedia({
                    video: { deviceId: { exact: deviceId2 }, width: { ideal: 640 }, height: { ideal: 360 } },
                    audio: false
                });
                localVideo2.srcObject = localStream2;
                localVideo2.classList.remove('d-none');
                document.getElementById('rotate-pip-btn').classList.remove('d-none');
            } catch (e) {}
        } else {
            document.getElementById('rotate-pip-btn').classList.add('d-none');
        }

        if (!pollTimer) startPolling();
    }

    deviceSelect.addEventListener('change', function() {
        var d2 = deviceSelect2.value || null;
        if (this.value) startCapture(this.value, d2);
    });
    deviceSelect2.addEventListener('change', function() {
        var d1 = deviceSelect.value;
        var d2 = this.value || null;
        if (d1) startCapture(d1, d2);
    });

    // ── WebRTC broadcaster logic ──

    function createPeerConnection(viewerId) {
        var pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });

        if (localStream) {
            localStream.getTracks().forEach(function(track) {
                pc.addTrack(track, localStream);
            });
        }
        if (localStream2) {
            localStream2.getTracks().forEach(function(track) {
                pc.addTrack(track, localStream2);
            });
        }

        pc.onicecandidate = function(e) {
            if (e.candidate) {
                sendSignal(viewerId, 'ice-candidate', JSON.stringify(e.candidate));
            }
        };

        pc.onconnectionstatechange = function() {
            if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed' || pc.connectionState === 'closed') {
                removePeer(viewerId);
            }
        };

        peers[viewerId] = { pc: pc };
        updateViewerCount();
        return pc;
    }

    function removePeer(viewerId) {
        if (peers[viewerId]) {
            try { peers[viewerId].pc.close(); } catch(e) {}
            delete peers[viewerId];
            updateViewerCount();
        }
    }

    function updateViewerCount() {
        viewerCount.textContent = Object.keys(peers).length;
    }

    async function handleSignal(signal) {
        var fromPeer = signal.from_peer;
        var type     = signal.type;
        var payload  = JSON.parse(signal.payload);

        if (type === 'offer') {
            var pc = createPeerConnection(fromPeer);
            await pc.setRemoteDescription(new RTCSessionDescription(payload));
            var answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            sendSignal(fromPeer, 'answer', JSON.stringify(answer));
        }
        else if (type === 'ice-candidate') {
            if (peers[fromPeer] && peers[fromPeer].pc) {
                try {
                    await peers[fromPeer].pc.addIceCandidate(new RTCIceCandidate(payload));
                } catch(e) {}
            }
        }
        else if (type === 'quality-change') {
            applyQuality(fromPeer, payload);
        }
    }

    var QUALITY_MAP = { '144p': 150000, '360p': 500000, '720p': 1500000, '1080p': 4000000, 'auto': 0 };
    function applyQuality(peerId, qualityObj) {
        var q = typeof qualityObj === 'string' ? qualityObj : (qualityObj.quality || 'auto');
        var maxBitrate = QUALITY_MAP[q] || 0;
        if (!peers[peerId]) return;
        var senders = peers[peerId].pc.getSenders();
        senders.forEach(function(sender) {
            if (!sender.track || sender.track.kind !== 'video') return;
            var params = sender.getParameters();
            if (!params.encodings || params.encodings.length === 0) {
                params.encodings = [{}];
            }
            if (maxBitrate > 0) {
                params.encodings[0].maxBitrate = maxBitrate;
            } else {
                delete params.encodings[0].maxBitrate;
            }
            sender.setParameters(params).catch(function() {});
        });
    }

    function sendSignal(toPeer, type, payload) {
        fetch(SIGNAL_POST, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ to_peer: toPeer, type: type, payload: payload })
        }).catch(function() {});
    }

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(pollSignals, 1500);
        pollSignals();
    }

    async function pollSignals() {
        try {
            var res = await fetch(SIGNAL_GET, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });
            var signals = await res.json();
            signals.forEach(handleSignal);
        } catch(e) {}
    }

    // ── Init ──
    loadDevices();

    window.addEventListener('beforeunload', function() {
        if (pollTimer) clearInterval(pollTimer);
        Object.keys(peers).forEach(function(pid) {
            try { peers[pid].pc.close(); } catch(e) {}
        });
    });
})();
</script>
@endsection
