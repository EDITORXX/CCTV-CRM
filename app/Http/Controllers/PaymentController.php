<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,upi,bank_transfer,cheque,card',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Payment::create(array_merge($validated, [
            'company_id' => session('current_company_id'),
            'invoice_id' => $invoice->id,
            'created_by' => auth()->id(),
        ]));

        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment recorded successfully.');
    }
}
