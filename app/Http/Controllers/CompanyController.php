<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function select()
    {
        $companies = auth()->user()->companies;

        if ($companies->count() === 1) {
            session(['current_company_id' => $companies->first()->id]);
            return redirect()->route('dashboard');
        }

        return view('company.select', compact('companies'));
    }

    public function set(Company $company)
    {
        $user = auth()->user();

        if (!$user->companies()->where('companies.id', $company->id)->exists()) {
            abort(403, 'You do not have access to this company.');
        }

        session(['current_company_id' => $company->id]);

        return redirect()->route('dashboard');
    }

    public function settings()
    {
        $company = Company::findOrFail(session('current_company_id'));

        return view('company.settings', compact('company'));
    }

    public function updateSettings(Request $request)
    {
        $company = Company::findOrFail(session('current_company_id'));

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'gstin' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'gst_enabled' => 'boolean',
            'invoice_prefix' => 'nullable|string|max:20',
            'warranty_default_months' => 'nullable|integer|min:0',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'payment_qr' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $validated['gst_enabled'] = $request->boolean('gst_enabled');

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }
        unset($validated['logo']);

        if ($request->hasFile('payment_qr')) {
            $path = $request->file('payment_qr')->store('qrcodes', 'public');
            $validated['payment_qr_path'] = $path;
        }
        unset($validated['payment_qr']);

        $company->update($validated);

        return redirect()->route('company.settings')->with('success', 'Company settings updated successfully.');
    }
}
