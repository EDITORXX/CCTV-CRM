<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\LiveStreamSignal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LiveStreamController extends Controller
{
    // ──────────────────────────────────────────────
    // Technician / Admin pages (auth + company)
    // ──────────────────────────────────────────────

    public function index()
    {
        $streams = LiveStream::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livestream.index', compact('streams'));
    }

    public function create()
    {
        return view('livestream.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'nullable|string|max:255',
            'password' => 'required|string|min:4|max:50',
        ]);

        $stream = LiveStream::create([
            'user_id'    => auth()->id(),
            'company_id' => session('current_company_id'),
            'token'      => LiveStream::generateToken(),
            'password'   => $request->password,
            'title'      => $request->title,
            'status'     => 'active',
            'started_at' => now(),
        ]);

        session(["_live_plain_pass_{$stream->id}" => $request->password]);

        return redirect()->route('livestream.show', $stream)
            ->with('success', 'Live stream started! Share the link below.');
    }

    public function show(LiveStream $livestream)
    {
        if ($livestream->user_id !== auth()->id()) {
            abort(403);
        }

        return view('livestream.show', [
            'stream'   => $livestream,
            'shareUrl' => route('livestream.viewer', $livestream->token),
        ]);
    }

    public function stop(LiveStream $livestream)
    {
        if ($livestream->user_id !== auth()->id()) {
            abort(403);
        }

        $livestream->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        $livestream->signals()->delete();

        return redirect()->route('livestream.index')
            ->with('success', 'Live stream ended.');
    }

    // ──────────────────────────────────────────────
    // Signaling API (technician side, auth required)
    // ──────────────────────────────────────────────

    public function storeSignal(Request $request, LiveStream $livestream)
    {
        if ($livestream->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'to_peer' => 'required|string|max:64',
            'type'    => 'required|string|in:offer,answer,ice-candidate',
            'payload' => 'required|string',
        ]);

        LiveStreamSignal::create([
            'live_stream_id' => $livestream->id,
            'from_peer'      => 'tech',
            'to_peer'        => $request->to_peer,
            'type'           => $request->type,
            'payload'        => $request->payload,
        ]);

        return response()->json(['ok' => true]);
    }

    public function getSignals(Request $request, LiveStream $livestream)
    {
        if ($livestream->user_id !== auth()->id()) {
            abort(403);
        }

        $signals = LiveStreamSignal::where('live_stream_id', $livestream->id)
            ->where('to_peer', 'tech')
            ->where('consumed', false)
            ->orderBy('id')
            ->get();

        LiveStreamSignal::whereIn('id', $signals->pluck('id'))->update(['consumed' => true]);

        return response()->json($signals->map(fn ($s) => [
            'id'        => $s->id,
            'from_peer' => $s->from_peer,
            'type'      => $s->type,
            'payload'   => $s->payload,
        ]));
    }

    // ──────────────────────────────────────────────
    // Public viewer routes (no auth)
    // ──────────────────────────────────────────────

    public function viewer(string $token)
    {
        $stream = LiveStream::withoutGlobalScopes()
            ->where('token', $token)
            ->firstOrFail();

        if (!$stream->isActive()) {
            return view('livestream.ended');
        }

        if (session("live_verified_{$stream->id}")) {
            return redirect()->route('livestream.watch', $token);
        }

        return view('livestream.password', compact('stream'));
    }

    public function verifyPassword(Request $request, string $token)
    {
        $stream = LiveStream::withoutGlobalScopes()
            ->where('token', $token)
            ->firstOrFail();

        if (!$stream->isActive()) {
            return view('livestream.ended');
        }

        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->password, $stream->password)) {
            return back()->withErrors(['password' => 'Wrong password.']);
        }

        session(["live_verified_{$stream->id}" => true]);
        session(["live_peer_id_{$stream->id}" => 'viewer_' . substr(md5(uniqid(mt_rand(), true)), 0, 12)]);

        return redirect()->route('livestream.watch', $token);
    }

    public function watch(string $token)
    {
        $stream = LiveStream::withoutGlobalScopes()
            ->where('token', $token)
            ->firstOrFail();

        if (!$stream->isActive()) {
            return view('livestream.ended');
        }

        if (!session("live_verified_{$stream->id}")) {
            return redirect()->route('livestream.viewer', $token);
        }

        $peerId = session("live_peer_id_{$stream->id}");

        return view('livestream.watch', compact('stream', 'peerId'));
    }

    // Viewer signaling

    public function viewerSignal(Request $request, string $token)
    {
        $stream = LiveStream::withoutGlobalScopes()
            ->where('token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        if (!session("live_verified_{$stream->id}")) {
            abort(403);
        }

        $peerId = session("live_peer_id_{$stream->id}");

        $request->validate([
            'to_peer' => 'required|string|max:64',
            'type'    => 'required|string|in:offer,answer,ice-candidate',
            'payload' => 'required|string',
        ]);

        LiveStreamSignal::create([
            'live_stream_id' => $stream->id,
            'from_peer'      => $peerId,
            'to_peer'        => $request->to_peer,
            'type'           => $request->type,
            'payload'        => $request->payload,
        ]);

        return response()->json(['ok' => true]);
    }

    public function viewerGetSignals(Request $request, string $token)
    {
        $stream = LiveStream::withoutGlobalScopes()
            ->where('token', $token)
            ->firstOrFail();

        if (!session("live_verified_{$stream->id}")) {
            abort(403);
        }

        $peerId = session("live_peer_id_{$stream->id}");

        $signals = LiveStreamSignal::where('live_stream_id', $stream->id)
            ->where('to_peer', $peerId)
            ->where('consumed', false)
            ->orderBy('id')
            ->get();

        LiveStreamSignal::whereIn('id', $signals->pluck('id'))->update(['consumed' => true]);

        $active = $stream->isActive();

        return response()->json([
            'active'  => $active,
            'signals' => $signals->map(fn ($s) => [
                'id'        => $s->id,
                'from_peer' => $s->from_peer,
                'type'      => $s->type,
                'payload'   => $s->payload,
            ]),
        ]);
    }

    public function status(string $token)
    {
        $stream = LiveStream::withoutGlobalScopes()
            ->where('token', $token)
            ->firstOrFail();

        return response()->json(['active' => $stream->isActive()]);
    }
}
