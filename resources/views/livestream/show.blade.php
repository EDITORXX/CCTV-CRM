@extends('layouts.app')

@section('title', 'Live Stream — Broadcasting')

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
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="bi bi-broadcast-pin text-danger me-1"></i>
                {{ $stream->title ?: 'Live Stream' }}
            </h4>
            <form method="POST" action="{{ route('livestream.stop', $stream) }}" id="stop-form">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('Stop this live stream?')">
                    <i class="bi bi-stop-circle me-1"></i> Stop Stream
                </button>
            </form>
        </div>

        <div class="card mb-3">
            <div class="card-body p-0">
                <div class="broadcast-container">
                    <div class="live-badge"><i class="bi bi-circle-fill" style="font-size:.5rem;"></i> LIVE</div>
                    <div class="viewers-badge"><i class="bi bi-people-fill me-1"></i> <span id="viewer-count">0</span> viewers</div>
                    <video id="local-video" autoplay muted playsinline></video>
                </div>
            </div>
        </div>

        <div id="device-row" class="mb-3">
            <label class="form-label fw-semibold">Video Source</label>
            <select id="device-select" class="form-select">
                <option>Loading...</option>
            </select>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold"><i class="bi bi-share me-1"></i> Share Stream</div>
            <div class="card-body">
                <div class="share-box mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Stream Link</label>
                    <div class="share-url" id="share-url">{{ $shareUrl }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Password</label>
                    <div class="share-url" id="share-pass">{{ session('_live_plain_pass_' . $stream->id, '****') }}</div>
                </div>
                <button type="button" class="btn btn-primary w-100 mb-2" id="copy-btn">
                    <i class="bi bi-clipboard me-1"></i> Copy Link & Password
                </button>
                <button type="button" class="btn btn-success w-100" id="whatsapp-btn">
                    <i class="bi bi-whatsapp me-1"></i> Share via WhatsApp
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-info-circle me-1"></i> Stream Info</div>
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

    var localVideo   = document.getElementById('local-video');
    var deviceSelect = document.getElementById('device-select');
    var viewerCount  = document.getElementById('viewer-count');
    var localStream  = null;
    var peers        = {};
    var pollTimer    = null;

    var ICE_SERVERS = [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' }
    ];

    // ── Copy / Share ──

    document.getElementById('copy-btn').addEventListener('click', function() {
        var pass = document.getElementById('share-pass').textContent;
        var text = 'Live CCTV Stream\nLink: ' + SHARE_URL + '\nPassword: ' + pass;
        navigator.clipboard.writeText(text).then(function() {
            var btn = document.getElementById('copy-btn');
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
            setTimeout(function() { btn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Link & Password'; }, 2000);
        });
    });

    document.getElementById('whatsapp-btn').addEventListener('click', function() {
        var pass = document.getElementById('share-pass').textContent;
        var text = 'Live CCTV Stream\nLink: ' + SHARE_URL + '\nPassword: ' + pass;
        window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
    });

    // ── Device listing & preview ──

    async function loadDevices() {
        try { await navigator.mediaDevices.getUserMedia({ video: true }); } catch(e) {}
        var devices = await navigator.mediaDevices.enumerateDevices();
        var videoDevices = devices.filter(function(d) { return d.kind === 'videoinput'; });
        deviceSelect.innerHTML = '';
        videoDevices.forEach(function(d, i) {
            var opt = document.createElement('option');
            opt.value = d.deviceId;
            opt.textContent = d.label || ('Camera ' + (i + 1));
            deviceSelect.appendChild(opt);
        });
        if (videoDevices.length > 0) startCapture(videoDevices[0].deviceId);
    }

    async function startCapture(deviceId) {
        if (localStream) localStream.getTracks().forEach(function(t) { t.stop(); });
        localStream = await navigator.mediaDevices.getUserMedia({
            video: { deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: true
        });
        localVideo.srcObject = localStream;

        Object.keys(peers).forEach(function(pid) {
            var senders = peers[pid].pc.getSenders();
            localStream.getTracks().forEach(function(track) {
                var sender = senders.find(function(s) { return s.track && s.track.kind === track.kind; });
                if (sender) sender.replaceTrack(track);
            });
        });

        startPolling();
    }

    deviceSelect.addEventListener('change', function() {
        if (this.value) startCapture(this.value);
    });

    // ── WebRTC broadcaster logic ──

    function createPeerConnection(viewerId) {
        var pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });

        if (localStream) {
            localStream.getTracks().forEach(function(track) {
                pc.addTrack(track, localStream);
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
