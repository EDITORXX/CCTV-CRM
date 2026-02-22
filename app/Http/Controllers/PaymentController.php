<?php

namespace App\Http\Controllers;

use App\Models\CustomerAdvance;
use App\Models\CustomerAdvanceAllocation;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $balanceDue = $invoice->total - $invoice->payments()->sum('amount');
        $customer = $invoice->customer;
        $advanceBalance = $customer->getAdvanceBalance();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'use_advance' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,upi,bank_transfer,cheque,card',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $amount = (float) $validated['amount'];
        $useAdvance = (float) ($validated['use_advance'] ?? 0);

        if ($amount > $balanceDue) {
            return back()->withErrors(['amount' => 'Amount cannot exceed balance due (₹' . number_format($balanceDue, 2) . ').'])->withInput();
        }
        if ($useAdvance > 0 && $useAdvance > $advanceBalance) {
            return back()->withErrors(['use_advance' => 'Customer advance balance is only ₹' . number_format($advanceBalance, 2) . '.'])->withInput();
        }
        if ($useAdvance > $amount) {
            return back()->withErrors(['use_advance' => 'Use advance cannot exceed payment amount.'])->withInput();
        }

        DB::transaction(function () use ($invoice, $validated, $amount, $useAdvance) {
            $companyId = session('current_company_id');
            $paymentDate = $validated['payment_date'];
            $regularAmount = $amount - $useAdvance;

            if ($useAdvance > 0) {
                $advances = CustomerAdvance::where('customer_id', $invoice->customer_id)
                    ->where('company_id', $companyId)
                    ->with('allocations')
                    ->orderBy('id')
                    ->get();

                $remainingToAllocate = $useAdvance;
                foreach ($advances as $advance) {
                    if ($remainingToAllocate <= 0) {
                        break;
                    }
                    $rem = (float) $advance->amount - $advance->allocations->sum('amount');
                    if ($rem <= 0) {
                        continue;
                    }
                    $allocAmount = min($rem, $remainingToAllocate);
                    CustomerAdvanceAllocation::create([
                        'customer_advance_id' => $advance->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $allocAmount,
                    ]);
                    $remainingToAllocate -= $allocAmount;
                }

                Payment::create([
                    'company_id' => $companyId,
                    'invoice_id' => $invoice->id,
                    'amount' => $useAdvance,
                    'payment_date' => $paymentDate,
                    'payment_method' => 'advance_adjusted',
                    'reference_number' => 'Advance adjusted',
                    'notes' => $validated['notes'] ? 'Advance: ' . $validated['notes'] : null,
                    'created_by' => auth()->id(),
                ]);
            }

            if ($regularAmount > 0) {
                Payment::create([
                    'company_id' => $companyId,
                    'invoice_id' => $invoice->id,
                    'amount' => $regularAmount,
                    'payment_date' => $paymentDate,
                    'payment_method' => $validated['payment_method'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }
        });

        $totalPaid = $invoice->fresh()->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial']);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Payment recorded successfully.');
    }
}
