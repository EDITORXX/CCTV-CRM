<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\Warranty;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    private function getCustomer()
    {
        $user = auth()->user();

        return Customer::where('company_id', session('current_company_id'))
            ->where(function ($q) use ($user) {
                $q->where('email', $user->email)
                  ->orWhere('phone', $user->phone);
            })->firstOrFail();
    }

    public function dashboard()
    {
        $customer = $this->getCustomer();

        $stats = [
            'total_invoices' => $customer->invoices()->count(),
            'pending_invoices' => $customer->invoices()->whereIn('status', ['draft', 'sent'])->count(),
            'active_warranties' => $customer->warranties()->where('status', 'active')->where('end_date', '>', now())->count(),
            'open_complaints' => $customer->tickets()->whereNotIn('status', ['closed', 'resolved'])->count(),
        ];

        $recentInvoices = $customer->invoices()->latest()->take(5)->get();
        $recentTickets = $customer->tickets()->latest()->take(5)->get();

        return view('portal.dashboard', compact('customer', 'stats', 'recentInvoices', 'recentTickets'));
    }

    public function invoices()
    {
        $customer = $this->getCustomer();
        $invoices = $customer->invoices()->latest()->paginate(20);

        return view('portal.invoices', compact('invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $customer = $this->getCustomer();

        if ($invoice->customer_id !== $customer->id) {
            abort(403);
        }

        $invoice->load(['items.product', 'payments']);

        return view('portal.invoice-show', compact('invoice'));
    }

    public function warranties()
    {
        $customer = $this->getCustomer();
        $warranties = Warranty::where('customer_id', $customer->id)
            ->where('company_id', session('current_company_id'))
            ->with(['product', 'serialNumber'])
            ->latest()
            ->paginate(20);

        return view('portal.warranties', compact('warranties'));
    }

    public function complaints()
    {
        $customer = $this->getCustomer();
        $tickets = $customer->tickets()
            ->with(['site', 'updates'])
            ->latest()
            ->paginate(20);

        return view('portal.complaints', compact('tickets'));
    }

    public function storeComplaint(Request $request)
    {
        $customer = $this->getCustomer();

        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'complaint_type' => 'required|string|in:breakdown,maintenance,installation,other',
            'description' => 'required|string',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
        ]);

        $ticketNumber = 'TKT-' . strtoupper(uniqid());

        Ticket::create([
            'company_id' => session('current_company_id'),
            'ticket_number' => $ticketNumber,
            'customer_id' => $customer->id,
            'site_id' => $validated['site_id'],
            'complaint_type' => $validated['complaint_type'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('portal.complaints')->with('success', 'Complaint submitted successfully.');
    }
}
