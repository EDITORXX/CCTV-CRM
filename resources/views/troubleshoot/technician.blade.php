@extends('layouts.app')

@section('title', 'Troubleshoot — Customer Camera')

@section('styles')
<style>
    .video-wrap {
        background: #000;
        border-radius: .75rem;
        aspect-ratio: 16/9;
        position: relative;
        overflow: hidden;
    }
    .video-wrap video { width: 100%; height: 100%; object-fit: contain; }
    .remote-pip {
        position: absolute; top: 1rem; right: 1rem;
        width: 25%; max-width: 220px; border: 2px solid rgba(255,255,255,.5); border-radius: 8px;
        object-fit: cover; z-index: 5;
    }
    .live-badge {
        position: absolute; top: 1rem; left: 1rem;
        background: #dc3545; color: #fff; padding: .25rem .75rem; border-radius: 2rem;
        font-size: .8rem; font-weight: 600; z-index: 10;
    }
    .status-overlay {
        position: absolute; inset: 0;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: rgba(0,0,0,.85); z-index: 20;
    }
    .status-overlay.hidden { display: none; }
    .stream-controls { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
    .recording-dot { width: 8px; height: 8px; background: #dc3545; border-radius: 50%; animation: blink 1s infinite; }
    @keyframes blink { 50% { opacity: .4; } }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-tools me-1"></i> Customer Camera — Code {{ $session->short_code }}</h4>
    <a href="{{ route('troubleshoot.connect') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<div class="card mb-3">
    <div class="card-body p-0">
        <div class="video-wrap" id="video-rotate-wrap" data-rotation="0">
            <div class="live-badge d-none" id="live-badge"><i class="bi bi-circle-fill" style="font-size:.5rem;"></i> LIVE</div>
            <div class="status-overlay" id="status-connecting">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p class="fw-semibold text-white">Connecting to customer camera...</p>
            </div>
            <div class="status-overlay hidden" id="status-ended">
                <i class="bi bi-camera-video-off" style="font-size:3rem;opacity:.4;color:#fff;"></i>
                <h4 class="mt-3 fw-bold text-white">Session Ended</h4>
                <p class="text-white-50">Customer has ended the troubleshoot session.</p>
            </div>
            <video id="remote-video" autoplay playsinline></video>
            <video id="remote-video-pip" autoplay playsinline class="remote-pip d-none"></video>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="rotate-btn"><i class="bi bi-arrow-clockwise me-1"></i> Rotate</button>
    <button type="button" class="btn btn-outline-danger btn-sm" id="record-btn" disabled><i class="bi bi-record-circle me-1"></i> Record</button>
    <button type="button" class="btn btn-outline-secondary btn-sm" id="snap-btn" disabled><i class="bi bi-camera me-1"></i> Snap</button>
    <button type="button" class="btn btn-outline-secondary btn-sm" id="mute-btn"><i class="bi bi-volume-mute me-1"></i> Mute</button>
    <span id="record-indicator" class="d-none align-items-center gap-1"><span class="recording-dot"></span><small>Recording</small></span>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var CODE = '{{ $session->short_code }}';
    var SIGNAL_POST = '{{ route("troubleshoot.tech.signal", $session->short_code) }}';
    var SIGNAL_GET  = '{{ route("troubleshoot.tech.signals", $session->short_code) }}';
    var CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var remoteVideo = document.getElementById('remote-video');
    var remoteVideoPip = document.getElementById('remote-video-pip');
    var videoTrackCount = 0;
    var connecting = document.getElementById('status-connecting');
    var ended = document.getElementById('status-ended');
    var liveBadge = document.getElementById('live-badge');
    var videoWrap = document.getElementById('video-rotate-wrap');

    var pc = null;
    var pollTimer = null;
    var ICE_SERVERS = [{ urls: 'stun:stun.l.google.com:19302' }, { urls: 'stun:stun1.l.google.com:19302' }];

    var rotations = [0, 90, 180, 270];
    document.getElementById('rotate-btn').addEventListener('click', function() {
        var current = parseInt(videoWrap.getAttribute('data-rotation') || '0', 10);
        var idx = (rotations.indexOf(current) + 1) % 4;
        var deg = rotations[idx];
        videoWrap.setAttribute('data-rotation', deg);
        videoWrap.style.transform = 'rotate(' + deg + 'deg)';
    });

    var viewerRecorder = null, viewerRecordChunks = [];
    document.getElementById('record-btn').addEventListener('click', function() {
        var btn = this, ind = document.getElementById('record-indicator');
        var str = remoteVideo.srcObject;
        if (!str || !str.getTracks().length) return;
        if (viewerRecorder && viewerRecorder.state === 'recording') {
            viewerRecorder.stop();
            ind.classList.add('d-none');
            btn.innerHTML = '<i class="bi bi-record-circle me-1"></i> Record';
            return;
        }
        viewerRecordChunks = [];
        var mime = MediaRecorder.isTypeSupported('video/webm;codecs=vp9') ? 'video/webm;codecs=vp9' : 'video/webm';
        viewerRecorder = new MediaRecorder(str, { mimeType: mime });
        viewerRecorder.ondataavailable = function(e) { if (e.data.size) viewerRecordChunks.push(e.data); };
        viewerRecorder.onstop = function() {
            var blob = new Blob(viewerRecordChunks, { type: 'video/webm' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'troubleshoot-' + Date.now() + '.webm';
            a.click();
            URL.revokeObjectURL(a.href);
        };
        viewerRecorder.start();
        ind.classList.remove('d-none');
        ind.style.display = 'flex';
        btn.innerHTML = '<i class="bi bi-stop-circle me-1"></i> Stop Record';
    });

    document.getElementById('snap-btn').addEventListener('click', function() {
        var v = remoteVideo;
        if (!v.videoWidth) return;
        var c = document.createElement('canvas');
        c.width = v.videoWidth;
        c.height = v.videoHeight;
        c.getContext('2d').drawImage(v, 0, 0);
        c.toBlob(function(blob) {
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'troubleshoot-snap-' + Date.now() + '.png';
            a.click();
            URL.revokeObjectURL(a.href);
        }, 'image/png');
    });

    document.getElementById('mute-btn').addEventListener('click', function() {
        var btn = this;
        remoteVideo.muted = !remoteVideo.muted;
        btn.innerHTML = remoteVideo.muted ? '<i class="bi bi-volume-mute me-1"></i> Unmute' : '<i class="bi bi-volume-up me-1"></i> Mute';
    });

    function sendSignal(toPeer, type, payload) {
        return fetch(SIGNAL_POST, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ to_peer: toPeer, type: type, payload: payload })
        });
    }

    async function pollSignals() {
        try {
            var res = await fetch(SIGNAL_GET, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
            var data = await res.json();
            if (!data.active) {
                connecting.classList.add('hidden');
                ended.classList.remove('hidden');
                liveBadge.classList.add('d-none');
                if (pollTimer) clearInterval(pollTimer);
                return;
            }
            data.signals.forEach(handleSignal);
        } catch (e) {}
    }

    async function handleSignal(signal) {
        var type = signal.type, payload = JSON.parse(signal.payload);
        if (type === 'answer' && pc && pc.signalingState === 'have-local-offer') {
            await pc.setRemoteDescription(new RTCSessionDescription(payload));
        } else if (type === 'ice-candidate' && pc) {
            try { await pc.addIceCandidate(new RTCIceCandidate(payload)); } catch (e) {}
        }
    }

    async function startViewing() {
        pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });
        pc.onicecandidate = function(e) {
            if (e.candidate) sendSignal('customer', 'ice-candidate', JSON.stringify(e.candidate));
        };
        pc.ontrack = function(e) {
            if (e.track.kind !== 'video') return;
            videoTrackCount++;
            if (videoTrackCount === 1) {
                if (e.streams && e.streams[0]) {
                    remoteVideo.srcObject = e.streams[0];
                    remoteVideoPip.classList.add('d-none');
                }
                document.getElementById('record-btn').disabled = false;
                document.getElementById('snap-btn').disabled = false;
            } else {
                var pipStream = new MediaStream([e.track]);
                remoteVideoPip.srcObject = pipStream;
                remoteVideoPip.classList.remove('d-none');
            }
        };
        pc.onconnectionstatechange = function() {
            if (pc.connectionState === 'connected') {
                connecting.classList.add('hidden');
                liveBadge.classList.remove('d-none');
            }
        };
        pc.addTransceiver('video', { direction: 'recvonly' });
        pc.addTransceiver('audio', { direction: 'recvonly' });
        var offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        await sendSignal('customer', 'offer', JSON.stringify(offer));
        pollTimer = setInterval(pollSignals, 1500);
    }

    startViewing();
    window.addEventListener('beforeunload', function() {
        if (pollTimer) clearInterval(pollTimer);
        if (pc) try { pc.close(); } catch(e) {}
    });
})();
</script>
@endsection
