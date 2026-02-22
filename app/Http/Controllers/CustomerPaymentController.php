<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Models\Payment;
use Illuminate\Http\Request;

class CustomerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerPayment::where('company_id', session('current_company_id'))
            ->with(['customer', 'invoice', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(25);
        $pendingCount = CustomerPayment::where('company_id', session('current_company_id'))
            ->where('status', 'pending')->count();

        return view('customer-payments.index', compact('payments', 'pendingCount'));
    }

    public function approve(CustomerPayment $customerPayment)
    {
        $customerPayment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Payment::create([
            'company_id' => $customerPayment->company_id,
            'invoice_id' => $customerPayment->invoice_id,
            'amount' => $customerPayment->amount,
            'payment_date' => $customerPayment->created_at->toDateString(),
            'payment_method' => 'upi',
            'reference_number' => 'CP-' . $customerPayment->id,
            'notes' => 'Customer submitted payment (approved)',
            'created_by' => auth()->id(),
        ]);

        $invoice = $customerPayment->invoice;
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        }

        return back()->with('success', 'Payment approved and recorded.');
    }

    public function reject(Request $request, CustomerPayment $customerPayment)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $customerPayment->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payment rejected.');
    }
}
