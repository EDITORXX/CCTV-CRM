<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class PublicInvoiceController extends Controller
{
    // Public shareable Bill of Supply view
    public function show(string $token)
    {
        $invoice = Invoice::where('share_token', $token)
            ->with(['customer', 'site', 'items.product', 'items.serialNumbers', 'payments'])
            ->firstOrFail();

        $company = \App\Models\Company::find($invoice->company_id);
        $totalPaid = $invoice->payments->sum('amount');
        $balance = $invoice->total - $totalPaid;

        return view('invoices.public', compact('invoice', 'company', 'totalPaid', 'balance', 'token'));
    }

    // Customer digitally signs the invoice
    public function sign(\Illuminate\Http\Request $request, string $token)
    {
        $invoice = Invoice::where('share_token', $token)->firstOrFail();

        $request->validate([
            'signature' => 'required|string', // base64 canvas image
        ]);

        // Get real client IP (handle proxies)
        $ip = $request->header('X-Forwarded-For')
            ? explode(',', $request->header('X-Forwarded-For'))[0]
            : $request->ip();

        $invoice->update([
            'customer_signature' => $request->signature,
            'customer_ip'        => trim($ip),
            'customer_signed_at' => now(),
        ]);

        return response()->json([
            'success'   => true,
            'signed_at' => now()->format('d M Y, h:i A'),
            'ip'        => trim($ip),
        ]);
    }

    // Public Terms & Conditions page with language toggle + signature
    public function terms(string $token)
    {
        $invoice = Invoice::where('share_token', $token)
            ->with(['customer', 'site', 'payments'])
            ->firstOrFail();

        $company = \App\Models\Company::find($invoice->company_id);

        return view('terms.public', compact('invoice', 'company', 'token'));
    }

    // Generate / return share token (called from invoice show page)
    public function generateToken(Invoice $invoice)
    {
        if (!$invoice->share_token) {
            $invoice->update(['share_token' => Invoice::generateShareToken()]);
        }

        return response()->json([
            'token' => $invoice->share_token,
            'url'   => route('invoice.public.show', $invoice->share_token),
        ]);
    }
}
