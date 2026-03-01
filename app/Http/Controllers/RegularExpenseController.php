<?php

namespace App\Http\Controllers;

use App\Helpers\CompanyHelper;
use App\Models\ExpenseCategory;
use App\Models\RegularExpense;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegularExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = RegularExpense::with(['expenseCategory', 'creator']);

        $creatorIds = CompanyHelper::expenseVisibleCreatorIds();
        if ($creatorIds !== null) {
            $query->whereIn('created_by', $creatorIds);
        }

        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20)->withQueryString();
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('regular-expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('regular-expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => [
                'required',
                Rule::exists('expense_categories', 'id')->where('company_id', session('current_company_id')),
            ],
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        RegularExpense::create(array_merge($validated, [
            'company_id' => session('current_company_id'),
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('regular-expenses.index')->with('success', 'Regular expense recorded successfully.');
    }

    public function edit(RegularExpense $regular_expense)
    {
        $this->authorizeExpenseAccess($regular_expense->created_by);

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('regular-expenses.edit', compact('regular_expense', 'categories'));
    }

    public function update(Request $request, RegularExpense $regular_expense)
    {
        $this->authorizeExpenseAccess($regular_expense->created_by);

        $validated = $request->validate([
            'expense_category_id' => [
                'required',
                Rule::exists('expense_categories', 'id')->where('company_id', session('current_company_id')),
            ],
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $regular_expense->update($validated);

        return redirect()->route('regular-expenses.index')->with('success', 'Regular expense updated.');
    }

    public function destroy(RegularExpense $regular_expense)
    {
        $this->authorizeExpenseAccess($regular_expense->created_by);

        $regular_expense->delete();

        return redirect()->route('regular-expenses.index')->with('success', 'Regular expense deleted.');
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
