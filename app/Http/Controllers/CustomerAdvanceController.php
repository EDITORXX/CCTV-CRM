<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAdvance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerAdvanceController extends Controller
{
    public function index(Request $request)
    {
        $query = CustomerAdvance::with(['customer', 'creator']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $advances = $query->with(['customer', 'creator', 'allocations'])->latest('payment_date')->latest('id')->paginate(20);
        $customers = Customer::orderBy('name')->get();

        return view('customer-advances.index', compact('advances', 'customers'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $company = Company::find(session('current_company_id'));

        $lastAdvance = CustomerAdvance::where('company_id', session('current_company_id'))
            ->orderBy('id', 'desc')->first();
        $nextNumber = 'ADV-' . str_pad(
            ($lastAdvance ? intval(preg_replace('/\D/', '', $lastAdvance->receipt_number)) + 1 : 1),
            5, '0', STR_PAD_LEFT
        );

        return view('customer-advances.create', compact('customers', 'company', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'receipt_number' => 'required|string|max:50',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,upi,bank_transfer,cheque,card',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $companyId = session('current_company_id');

        $exists = CustomerAdvance::where('company_id', $companyId)
            ->where('receipt_number', $validated['receipt_number'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['receipt_number' => 'This receipt number already exists for this company.']);
        }

        $advance = CustomerAdvance::create(array_merge($validated, [
            'company_id' => $companyId,
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('customer-advances.show', $advance)
            ->with('success', 'Advance recorded. Receipt can be printed from the detail page.');
    }

    public function show(CustomerAdvance $customerAdvance)
    {
        $customerAdvance->load(['customer', 'allocations.invoice', 'creator']);
        return view('customer-advances.show', compact('customerAdvance'));
    }

    public function receipt(CustomerAdvance $customerAdvance)
    {
        $customerAdvance->load(['customer']);
        $company = Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('pdf.advance-receipt', compact('customerAdvance', 'company'));
        return $pdf->stream('advance-receipt-' . $customerAdvance->receipt_number . '.pdf');
    }

    public function download(CustomerAdvance $customerAdvance)
    {
        $customerAdvance->load(['customer']);
        $company = Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('pdf.advance-receipt', compact('customerAdvance', 'company'));
        return $pdf->download('advance-receipt-' . $customerAdvance->receipt_number . '.pdf');
    }
}
