<?php

namespace App\Http\Controllers;

use App\Helpers\CompanyHelper;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\RegularExpense;
use App\Models\SiteExpense;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    public function index(Request $request)
    {
        $creatorIds = CompanyHelper::expenseVisibleCreatorIds();

        $regQuery = RegularExpense::with(['expenseCategory', 'creator']);
        if ($creatorIds !== null) {
            $regQuery->whereIn('created_by', $creatorIds);
        }
        if ($request->filled('reg_from')) {
            $regQuery->whereDate('expense_date', '>=', $request->reg_from);
        }
        if ($request->filled('reg_to')) {
            $regQuery->whereDate('expense_date', '<=', $request->reg_to);
        }
        if ($request->filled('expense_category_id')) {
            $regQuery->where('expense_category_id', $request->expense_category_id);
        }
        $regularExpenses = $regQuery->orderBy('expense_date', 'desc')->paginate(15, ['*'], 'reg_page')->withQueryString();

        $siteQuery = SiteExpense::with(['customer', 'site', 'creator']);
        if ($creatorIds !== null) {
            $siteQuery->whereIn('created_by', $creatorIds);
        }
        if ($request->filled('site_from')) {
            $siteQuery->whereDate('expense_date', '>=', $request->site_from);
        }
        if ($request->filled('site_to')) {
            $siteQuery->whereDate('expense_date', '<=', $request->site_to);
        }
        if ($request->filled('customer_id')) {
            $siteQuery->where('customer_id', $request->customer_id);
        }
        $siteExpenses = $siteQuery->orderBy('expense_date', 'desc')->paginate(15, ['*'], 'site_page')->withQueryString();

        $categories = ExpenseCategory::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return view('expenses.index', compact('regularExpenses', 'siteExpenses', 'categories', 'customers'));
    }
}
