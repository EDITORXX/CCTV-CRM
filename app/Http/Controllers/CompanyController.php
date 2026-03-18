<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyDocumentLayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    private const DOCUMENT_TYPES = ['estimate', 'invoice', 'advance_receipt'];

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

    public function create()
    {
        $user = auth()->user();
        $hasCompanies = $user->companies()->exists();
        $canCreate = !$hasCompanies || $user->hasRole(['company_admin', 'super_admin']);
        if (!$canCreate) {
            abort(403, 'You do not have permission to create a company.');
        }
        return view('company.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $hasCompanies = $user->companies()->exists();
        $canCreate = !$hasCompanies || $user->hasRole(['company_admin', 'super_admin']);
        if (!$canCreate) {
            abort(403, 'You do not have permission to create a company.');
        }

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
        ]);

        $validated['gst_enabled'] = $request->boolean('gst_enabled');
        $validated['invoice_prefix'] = $validated['invoice_prefix'] ?? 'INV';
        $validated['warranty_default_months'] = $validated['warranty_default_months'] ?? 12;

        $company = Company::create($validated);

        // Company email se admin user: agar nahi hai to banao (password 123456), company_admin attach karo
        $adminEmail = $validated['email'] ?? null;
        if ($adminEmail) {
            $adminUser = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $validated['name'] ?? 'Admin',
                    'password' => Hash::make('123456'),
                    'phone' => $validated['phone'] ?? null,
                    'is_active' => true,
                ]
            );
            if (!$adminUser->hasRole('company_admin') && !$adminUser->hasRole('super_admin')) {
                $adminUser->assignRole('company_admin');
            }
            $company->users()->syncWithoutDetaching([$adminUser->id => ['role' => 'company_admin']]);
        }

        // Jo user abhi login hai usko bhi is company ka admin banao
        if (!$user->hasRole('company_admin') && !$user->hasRole('super_admin')) {
            $user->assignRole('company_admin');
        }
        $company->users()->syncWithoutDetaching([$user->id => ['role' => 'company_admin']]);

        session(['current_company_id' => $company->id]);

        return redirect()->route('dashboard')->with('success', 'Company created successfully.');
    }

    public function settings()
    {
        $company = Company::findOrFail(session('current_company_id'));
        $this->ensureDefaultDocumentLayouts($company);
        $layouts = $company->documentLayouts()->get()->keyBy('document_type');
        $documentModes = [];
        foreach (self::DOCUMENT_TYPES as $documentType) {
            $documentModes[$documentType] = $this->resolveModeFromLayout($layouts->get($documentType));
        }

        return view('company.settings', compact('company', 'documentModes'));
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
            'invoice_terms' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'payment_qr' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'signature' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'stamp' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'document_layouts' => 'nullable|array',
            'document_layouts.*.mode' => 'nullable|in:stamp_only,sign_only,both_separate,both_overlap,none',
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

        if ($request->hasFile('signature')) {
            $path = $request->file('signature')->store('signatures', 'public');
            $validated['signature_path'] = $path;
        }
        unset($validated['signature']);

        if ($request->hasFile('stamp')) {
            $path = $request->file('stamp')->store('stamps', 'public');
            $validated['stamp_path'] = $path;
        }
        unset($validated['stamp']);

        unset($validated['document_layouts']);

        $company->update($validated);

        $this->ensureDefaultDocumentLayouts($company);
        foreach (self::DOCUMENT_TYPES as $documentType) {
            $mode = $request->input("document_layouts.$documentType.mode", 'stamp_only');
            $layoutData = match ($mode) {
                'sign_only' => ['show_signature' => true, 'show_stamp' => false, 'layout_mode' => 'separate'],
                'both_overlap' => ['show_signature' => true, 'show_stamp' => true, 'layout_mode' => 'overlap'],
                'both_separate' => ['show_signature' => true, 'show_stamp' => true, 'layout_mode' => 'separate'],
                'none' => ['show_signature' => false, 'show_stamp' => false, 'layout_mode' => 'separate'],
                default => ['show_signature' => false, 'show_stamp' => true, 'layout_mode' => 'separate'],
            };

            CompanyDocumentLayout::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'document_type' => $documentType,
                ],
                $layoutData
            );
        }

        return redirect()->route('company.settings')->with('success', 'Company settings updated successfully.');
    }

    private function ensureDefaultDocumentLayouts(Company $company): void
    {
        foreach (self::DOCUMENT_TYPES as $documentType) {
            CompanyDocumentLayout::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'document_type' => $documentType,
                ],
                [
                    'show_signature' => false,
                    'show_stamp' => true,
                    'layout_mode' => 'separate',
                ]
            );
        }
    }

    private function resolveModeFromLayout(?CompanyDocumentLayout $layout): string
    {
        if (!$layout) {
            return 'stamp_only';
        }

        if ($layout->show_signature && $layout->show_stamp) {
            return $layout->layout_mode === 'overlap' ? 'both_overlap' : 'both_separate';
        }

        if ($layout->show_signature) {
            return 'sign_only';
        }

        if ($layout->show_stamp) {
            return 'stamp_only';
        }

        return 'none';
    }
}
