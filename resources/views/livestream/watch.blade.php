<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Stream — Watching</title>
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
    </style>
</head>
<body>
    <div class="video-wrap">
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
    </div>

    <div class="controls-bar">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-broadcast-pin text-danger"></i>
            <span class="stream-title">{{ $stream->title ?: 'Live CCTV Stream' }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-light btn-sm" id="fullscreen-btn" title="Fullscreen">
                <i class="bi bi-fullscreen"></i>
            </button>
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
            var el = document.querySelector('.video-wrap');
            if (el.requestFullscreen) el.requestFullscreen();
            else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
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
                if (e.streams && e.streams[0]) {
                    remoteVideo.srcObject = e.streams[0];
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
