<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\Product;
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
        $invoices = $customer->invoices()->with(['site', 'items.product'])->latest()->paginate(20);
        $products = Product::where('company_id', session('current_company_id'))->orderBy('name')->get();

        return view('portal.invoices', compact('invoices', 'products'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $customer = $this->getCustomer();

        if ($invoice->customer_id !== $customer->id) {
            abort(403);
        }

        $invoice->load(['items.product', 'payments']);
        $company = \App\Models\Company::find(session('current_company_id'));

        return view('portal.invoice-show', compact('invoice', 'company'));
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
            'description' => 'required|string',
            'photo' => 'required|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaints', 'public');
        }

        $firstSite = $customer->sites()->first();
        $ticketNumber = 'TKT-' . strtoupper(uniqid());

        $ticket = Ticket::create([
            'company_id' => session('current_company_id'),
            'ticket_number' => $ticketNumber,
            'customer_id' => $customer->id,
            'site_id' => $firstSite?->id,
            'complaint_type' => 'other',
            'description' => $validated['description'],
            'photo' => $photoPath,
            'priority' => 'medium',
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Complaint submitted successfully.',
                'ticket_number' => $ticketNumber,
                'description' => $validated['description'],
                'customer_name' => $customer->name,
                'photo_url' => $photoPath ? url('storage/' . $photoPath) : null,
            ]);
        }

        return redirect()->route('portal.complaints')->with('success', 'Complaint submitted successfully.');
    }

    public function payments()
    {
        $customer = $this->getCustomer();
        $customerPayments = CustomerPayment::where('customer_id', $customer->id)
            ->where('company_id', session('current_company_id'))
            ->with('invoice')
            ->latest()
            ->paginate(20);

        $unpaidInvoices = $customer->invoices()
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with('payments')
            ->orderBy('invoice_date', 'desc')
            ->get();

        $company = \App\Models\Company::find(session('current_company_id'));

        return view('portal.payments', compact('customerPayments', 'unpaidInvoices', 'company'));
    }

    public function storePayment(Request $request)
    {
        $customer = $this->getCustomer();

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'screenshot' => 'required|image|max:5120',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);
        if ($invoice->customer_id !== $customer->id) {
            abort(403);
        }

        $screenshotPath = $request->file('screenshot')->store('payment-screenshots', 'public');

        CustomerPayment::create([
            'company_id' => session('current_company_id'),
            'customer_id' => $customer->id,
            'invoice_id' => $validated['invoice_id'],
            'amount' => $validated['amount'],
            'screenshot' => $screenshotPath,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('portal.payments')->with('success', 'Payment submitted for approval.');
    }

    public function profile()
    {
        $customer = $this->getCustomer();
        $customer->load('sites');

        return view('portal.profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $customer = $this->getCustomer();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        return redirect()->route('portal.profile')->with('success', 'Profile updated successfully.');
    }
}
