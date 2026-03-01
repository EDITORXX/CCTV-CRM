<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories')->where('company_id', $companyId),
            ],
        ]);

        ExpenseCategory::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
        ]);

        return redirect()->route('expense-categories.index')->with('success', 'Category added.');
    }
}
