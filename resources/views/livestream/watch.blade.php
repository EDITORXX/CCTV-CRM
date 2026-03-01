<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CCTV View — Watching</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #000;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .video-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .video-wrap video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .live-indicator {
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
            z-index: 10;
            animation: pulse-live 2s infinite;
        }
        @keyframes pulse-live {
            0%, 100% { opacity: 1; }
            50% { opacity: .6; }
        }
        .status-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,.85);
            z-index: 20;
        }
        .status-overlay.hidden { display: none; }
        .status-overlay .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: .3rem;
        }
        .controls-bar {
            background: #111;
            padding: .75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-shrink: 0;
        }
        .controls-bar .stream-title {
            font-weight: 600;
            font-size: .9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .controls-bar .btn { font-size: .85rem; }
        .stream-controls { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
        .stream-controls .btn { font-size: .85rem; }
        .recording-dot { width: 8px; height: 8px; background: #dc3545; border-radius: 50%; animation: blink 1s infinite; }
        @keyframes blink { 50% { opacity: .4; } }
    </style>
</head>
<body>
    <div class="video-wrap" id="video-rotate-wrap" data-rotation="0">
        <div class="live-indicator" id="live-badge">
            <i class="bi bi-circle-fill" style="font-size:.5rem;"></i> LIVE
        </div>

        <div class="status-overlay" id="status-connecting">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="fw-semibold">Connecting to stream...</p>
            <p class="small text-white-50">Please wait</p>
        </div>

        <div class="status-overlay hidden" id="status-ended">
            <i class="bi bi-camera-video-off" style="font-size:3rem;opacity:.4;"></i>
            <h4 class="mt-3 fw-bold">Stream Ended</h4>
            <p class="text-white-50">The technician has stopped this stream.</p>
        </div>

        <div class="status-overlay hidden" id="status-error">
            <i class="bi bi-exclamation-triangle" style="font-size:3rem;color:#f59e0b;"></i>
            <h5 class="mt-3 fw-bold">Connection Failed</h5>
            <p class="text-white-50 mb-3" id="error-message">Could not connect to the stream.</p>
            <button class="btn btn-primary btn-sm" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Retry
            </button>
        </div>

        <video id="remote-video" autoplay playsinline></video>
        <video id="remote-video-pip" autoplay playsinline class="d-none" style="position:absolute;top:1rem;right:1rem;width:25%;max-width:220px;border:2px solid rgba(255,255,255,.5);border-radius:8px;object-fit:cover;z-index:5;"></video>
    </div>

    <div class="controls-bar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-camera-video text-danger"></i>
            <span class="stream-title">{{ $stream->title ?: 'CCTV View' }}</span>
        </div>
        <div class="d-flex align-items-center gap-2 stream-controls">
            <button type="button" class="btn btn-outline-light btn-sm" id="rotate-btn" title="Rotate"><i class="bi bi-arrow-clockwise me-1"></i> Rotate</button>
            <button type="button" class="btn btn-outline-light btn-sm" id="record-btn" title="Record" disabled><i class="bi bi-record-circle me-1"></i> Record</button>
            <button type="button" class="btn btn-outline-light btn-sm" id="snap-btn" title="Snapshot" disabled><i class="bi bi-camera me-1"></i> Snap</button>
            <button type="button" class="btn btn-outline-light btn-sm" id="mute-btn" title="Mute"><i class="bi bi-volume-mute me-1"></i> Mute</button>
            <button type="button" class="btn btn-outline-light btn-sm" id="fullscreen-btn" title="Fullscreen"><i class="bi bi-fullscreen"></i></button>
            <span id="record-indicator" class="d-none align-items-center gap-1" style="display:none;"><span class="recording-dot"></span><small>Recording</small></span>
        </div>
    </div>

    <script>
    (function() {
        var STREAM_TOKEN = '{{ $stream->token }}';
        var PEER_ID      = '{{ $peerId }}';
        var SIGNAL_POST  = '{{ route("livestream.viewer.signal", $stream->token) }}';
        var SIGNAL_GET   = '{{ route("livestream.viewer.signals", $stream->token) }}';
        var STATUS_URL   = '{{ route("livestream.status", $stream->token) }}';
        var CSRF         = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        var remoteVideo  = document.getElementById('remote-video');
        var remoteVideoPip = document.getElementById('remote-video-pip');
        var videoTrackCount = 0;
        var connecting   = document.getElementById('status-connecting');
        var ended        = document.getElementById('status-ended');
        var errorEl      = document.getElementById('status-error');
        var liveBadge    = document.getElementById('live-badge');

        var pc           = null;
        var pollTimer    = null;
        var statusTimer  = null;
        var connected    = false;
        var retries      = 0;
        var MAX_RETRIES  = 3;

        var ICE_SERVERS  = [
            { urls: 'stun:stun.l.google.com:19302' },
            { urls: 'stun:stun1.l.google.com:19302' }
        ];

        // ── Fullscreen ──
        document.getElementById('fullscreen-btn').addEventListener('click', function() {
            var el = document.getElementById('video-rotate-wrap');
            if (el.requestFullscreen) el.requestFullscreen();
            else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
        });

        // ── Rotation ──
        var videoWrap = document.getElementById('video-rotate-wrap');
        var rotations = [0, 90, 180, 270];
        document.getElementById('rotate-btn').addEventListener('click', function() {
            var current = parseInt(videoWrap.getAttribute('data-rotation') || '0', 10);
            var idx = (rotations.indexOf(current) + 1) % 4;
            var deg = rotations[idx];
            videoWrap.setAttribute('data-rotation', deg);
            videoWrap.style.transform = 'rotate(' + deg + 'deg)';
        });

        // ── Record (viewer) ──
        var viewerRecorder = null;
        var viewerRecordChunks = [];
        document.getElementById('record-btn').addEventListener('click', function() {
            var btn = this;
            var ind = document.getElementById('record-indicator');
            var str = remoteVideo.srcObject;
            if (!str || !str.getTracks().length) return;
            if (viewerRecorder && viewerRecorder.state === 'recording') {
                viewerRecorder.stop();
                ind.style.display = 'none';
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
                a.download = 'cctv-watch-' + Date.now() + '.webm';
                a.click();
                URL.revokeObjectURL(a.href);
            };
            viewerRecorder.start();
            ind.classList.remove('d-none');
            ind.style.display = 'flex';
            btn.innerHTML = '<i class="bi bi-stop-circle me-1"></i> Stop Record';
        });

        // ── Snap (viewer) ──
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
                a.download = 'cctv-snap-' + Date.now() + '.png';
                a.click();
                URL.revokeObjectURL(a.href);
            }, 'image/png');
        });

        // ── Mute (viewer = mute playback) ──
        document.getElementById('mute-btn').addEventListener('click', function() {
            var btn = this;
            remoteVideo.muted = !remoteVideo.muted;
            btn.innerHTML = remoteVideo.muted ? '<i class="bi bi-volume-mute me-1"></i> Unmute' : '<i class="bi bi-volume-up me-1"></i> Mute';
        });

        // ── Signaling helpers ──
        function sendSignal(toPeer, type, payload) {
            return fetch(SIGNAL_POST, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ to_peer: toPeer, type: type, payload: payload })
            });
        }

        async function pollSignals() {
            try {
                var res = await fetch(SIGNAL_GET, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                var data = await res.json();

                if (!data.active) {
                    showEnded();
                    return;
                }

                data.signals.forEach(handleSignal);
            } catch(e) {}
        }

        async function handleSignal(signal) {
            var type    = signal.type;
            var payload = JSON.parse(signal.payload);

            if (type === 'answer') {
                if (pc && pc.signalingState === 'have-local-offer') {
                    await pc.setRemoteDescription(new RTCSessionDescription(payload));
                }
            }
            else if (type === 'ice-candidate') {
                if (pc) {
                    try { await pc.addIceCandidate(new RTCIceCandidate(payload)); } catch(e) {}
                }
            }
        }

        // ── WebRTC viewer logic ──
        async function startViewing() {
            pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });

            pc.onicecandidate = function(e) {
                if (e.candidate) {
                    sendSignal('tech', 'ice-candidate', JSON.stringify(e.candidate));
                }
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
                    connected = true;
                    connecting.classList.add('hidden');
                    liveBadge.style.display = '';
                }
                else if (pc.connectionState === 'disconnected') {
                    if (connected) {
                        retries++;
                        if (retries <= MAX_RETRIES) {
                            reconnect();
                        } else {
                            checkStreamStatus();
                        }
                    }
                }
                else if (pc.connectionState === 'failed') {
                    retries++;
                    if (retries <= MAX_RETRIES) {
                        reconnect();
                    } else {
                        showError('Connection failed after multiple attempts.');
                    }
                }
            };

            pc.addTransceiver('video', { direction: 'recvonly' });
            pc.addTransceiver('audio', { direction: 'recvonly' });

            var offer = await pc.createOffer();
            await pc.setLocalDescription(offer);

            await sendSignal('tech', 'offer', JSON.stringify(offer));

            pollTimer = setInterval(pollSignals, 1500);

            statusTimer = setInterval(checkStreamStatus, 10000);
        }

        function reconnect() {
            if (pc) { try { pc.close(); } catch(e) {} pc = null; }
            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
            connected = false;
            connecting.classList.remove('hidden');
            connecting.querySelector('p').textContent = 'Reconnecting... (attempt ' + retries + ')';
            setTimeout(startViewing, 2000);
        }

        async function checkStreamStatus() {
            try {
                var res = await fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } });
                var data = await res.json();
                if (!data.active) showEnded();
            } catch(e) {}
        }

        function showEnded() {
            if (pollTimer) clearInterval(pollTimer);
            if (statusTimer) clearInterval(statusTimer);
            if (pc) { try { pc.close(); } catch(e) {} }
            connecting.classList.add('hidden');
            errorEl.classList.add('hidden');
            ended.classList.remove('hidden');
            liveBadge.style.display = 'none';
        }

        function showError(msg) {
            if (pollTimer) clearInterval(pollTimer);
            if (statusTimer) clearInterval(statusTimer);
            connecting.classList.add('hidden');
            document.getElementById('error-message').textContent = msg;
            errorEl.classList.remove('hidden');
            liveBadge.style.display = 'none';
        }

        // ── Init ──
        startViewing();

        window.addEventListener('beforeunload', function() {
            if (pollTimer) clearInterval(pollTimer);
            if (statusTimer) clearInterval(statusTimer);
            if (pc) { try { pc.close(); } catch(e) {} }
        });
    })();
    </script>
</body>
</html>
