<?php

namespace App\Http\Controllers;

use App\Models\TroubleshootSession;
use App\Models\TroubleshootSignal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TroubleshootController extends Controller
{
    // ──────────────────────────────────────────────
    // Customer (portal)
    // ──────────────────────────────────────────────

    public function customerPage()
    {
        return view('troubleshoot.customer');
    }

    public function start(Request $request)
    {
        $plainPin = TroubleshootSession::generatePin();
        $session = TroubleshootSession::create([
            'company_id'   => session('current_company_id'),
            'customer_id'  => auth()->id(),
            'short_code'   => TroubleshootSession::generateShortCode(),
            'password'     => $plainPin,
            'status'       => 'waiting',
            'started_at'   => now(),
        ]);

        session(["_troubleshoot_session_id" => $session->id]);
        session(["_troubleshoot_plain_pin_{$session->id}" => $plainPin]);

        return response()->json([
            'session_id' => $session->id,
            'short_code' => $session->short_code,
            'password'   => $plainPin,
        ]);
    }

    public function end(Request $request)
    {
        $sessionId = session('_troubleshoot_session_id');
        if (!$sessionId) {
            return response()->json(['ok' => false], 400);
        }

        $session = TroubleshootSession::where('id', $sessionId)
            ->where('customer_id', auth()->id())
            ->first();

        if ($session) {
            $session->update(['status' => 'ended', 'ended_at' => now()]);
            $session->signals()->delete();
        }

        session()->forget('_troubleshoot_session_id');
        return response()->json(['ok' => true]);
    }

    public function customerStoreSignal(Request $request, TroubleshootSession $troubleshoot)
    {
        if ($troubleshoot->customer_id !== auth()->id()) {
            abort(403);
        }
        if (!$troubleshoot->isActive()) {
            abort(404);
        }

        $request->validate([
            'to_peer' => 'required|string|max:64',
            'type'    => 'required|string|in:offer,answer,ice-candidate',
            'payload' => 'required|string',
        ]);

        TroubleshootSignal::create([
            'troubleshoot_session_id' => $troubleshoot->id,
            'from_peer'               => 'customer',
            'to_peer'                 => $request->to_peer,
            'type'                    => $request->type,
            'payload'                 => $request->payload,
        ]);

        return response()->json(['ok' => true]);
    }

    public function customerGetSignals(Request $request, TroubleshootSession $troubleshoot)
    {
        if ($troubleshoot->customer_id !== auth()->id()) {
            abort(403);
        }

        $signals = TroubleshootSignal::where('troubleshoot_session_id', $troubleshoot->id)
            ->where('to_peer', 'customer')
            ->where('consumed', false)
            ->orderBy('id')
            ->get();

        TroubleshootSignal::whereIn('id', $signals->pluck('id'))->update(['consumed' => true]);

        return response()->json([
            'active'  => $troubleshoot->isActive(),
            'signals' => $signals->map(fn ($s) => [
                'id'        => $s->id,
                'from_peer' => $s->from_peer,
                'type'      => $s->type,
                'payload'   => $s->payload,
            ]),
        ]);
    }

    // ──────────────────────────────────────────────
    // Technician (connect + watch)
    // ──────────────────────────────────────────────

    public function connectForm()
    {
        return view('troubleshoot.connect');
    }

    public function verifyAndWatch(Request $request)
    {
        $request->validate([
            'code'     => 'required|string|size:6',
            'password' => 'required|string|size:4',
        ]);

        $session = TroubleshootSession::withoutGlobalScopes()
            ->where('short_code', strtoupper($request->code))
            ->whereIn('status', ['waiting', 'active'])
            ->first();

        if (!$session || !Hash::check($request->password, $session->password)) {
            return back()->withErrors(['code' => 'Invalid code or password.']);
        }

        $session->update(['status' => 'active']);

        session(["_troubleshoot_tech_{$session->id}" => true]);

        return redirect()->route('troubleshoot.watch', $session->short_code);
    }

    public function technicianWatch(string $code)
    {
        $session = TroubleshootSession::withoutGlobalScopes()
            ->where('short_code', strtoupper($code))
            ->firstOrFail();

        if (!session("_troubleshoot_tech_{$session->id}")) {
            abort(403);
        }

        if (!$session->isActive()) {
            return view('troubleshoot.ended');
        }

        return view('troubleshoot.technician', compact('session'));
    }

    public function technicianStoreSignal(Request $request, string $code)
    {
        $session = TroubleshootSession::withoutGlobalScopes()
            ->where('short_code', strtoupper($code))
            ->firstOrFail();

        if (!session("_troubleshoot_tech_{$session->id}")) {
            abort(403);
        }

        $request->validate([
            'to_peer' => 'required|string|max:64',
            'type'    => 'required|string|in:offer,answer,ice-candidate',
            'payload' => 'required|string',
        ]);

        TroubleshootSignal::create([
            'troubleshoot_session_id' => $session->id,
            'from_peer'               => 'technician',
            'to_peer'                 => $request->to_peer,
            'type'                    => $request->type,
            'payload'                 => $request->payload,
        ]);

        return response()->json(['ok' => true]);
    }

    public function technicianGetSignals(Request $request, string $code)
    {
        $session = TroubleshootSession::withoutGlobalScopes()
            ->where('short_code', strtoupper($code))
            ->firstOrFail();

        if (!session("_troubleshoot_tech_{$session->id}")) {
            abort(403);
        }

        $signals = TroubleshootSignal::where('troubleshoot_session_id', $session->id)
            ->where('to_peer', 'technician')
            ->where('consumed', false)
            ->orderBy('id')
            ->get();

        TroubleshootSignal::whereIn('id', $signals->pluck('id'))->update(['consumed' => true]);

        return response()->json([
            'active'  => $session->isActive(),
            'signals' => $signals->map(fn ($s) => [
                'id'        => $s->id,
                'from_peer' => $s->from_peer,
                'type'      => $s->type,
                'payload'   => $s->payload,
            ]),
        ]);
    }
}
