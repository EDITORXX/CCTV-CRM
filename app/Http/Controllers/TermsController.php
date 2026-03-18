<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function index()
    {
        $company = Company::findOrFail(session('current_company_id'));
        return view('terms.manage', compact('company'));
    }

    public function update(Request $request)
    {
        $company = Company::findOrFail(session('current_company_id'));

        $request->validate([
            'invoice_terms'    => 'nullable|string',
            'invoice_terms_hi' => 'nullable|string',
        ]);

        $company->update([
            'invoice_terms'    => $request->input('invoice_terms'),
            'invoice_terms_hi' => $request->input('invoice_terms_hi'),
        ]);

        return redirect()->route('terms.index')->with('success', 'Terms & Conditions saved successfully.');
    }
}
