<?php

namespace App\Http\Controllers;

use App\Helpers\CompanyHelper;
use App\Models\Customer;
use App\Models\SiteExpense;
use Illuminate\Http\Request;

class SiteExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = SiteExpense::with(['customer', 'site', 'creator']);

        $creatorIds = CompanyHelper::expenseVisibleCreatorIds();
        if ($creatorIds !== null) {
            $query->whereIn('created_by', $creatorIds);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20)->withQueryString();
        $customers = Customer::orderBy('name')->get();

        return view('site-expenses.index', compact('expenses', 'customers'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        return view('site-expenses.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'site_id' => 'required|exists:sites,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'technician_name' => 'nullable|string|max:255',
        ]);

        SiteExpense::create(array_merge($validated, [
            'company_id' => session('current_company_id'),
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('site-expenses.index')->with('success', 'Site expense recorded successfully.');
    }

    public function edit(SiteExpense $site_expense)
    {
        $this->authorizeExpenseAccess($site_expense->created_by);

        $customers = Customer::orderBy('name')->get();
        $site_expense->load('site');

        return view('site-expenses.edit', compact('site_expense', 'customers'));
    }

    public function update(Request $request, SiteExpense $site_expense)
    {
        $this->authorizeExpenseAccess($site_expense->created_by);

        $validated = $request->validate([
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'technician_name' => 'nullable|string|max:255',
        ]);

        $site_expense->update($validated);

        return redirect()->route('site-expenses.index')->with('success', 'Site expense updated.');
    }

    public function destroy(SiteExpense $site_expense)
    {
        $this->authorizeExpenseAccess($site_expense->created_by);

        $site_expense->delete();

        return redirect()->route('site-expenses.index')->with('success', 'Site expense deleted.');
    }

    private function authorizeExpenseAccess(?int $createdBy): void
    {
        $creatorIds = CompanyHelper::expenseVisibleCreatorIds();
        if ($creatorIds === null) {
            return;
        }
        if ($createdBy === null || !in_array($createdBy, $creatorIds)) {
            abort(403, 'You do not have permission to access this expense.');
        }
    }
}
