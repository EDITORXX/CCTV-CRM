<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Customer $customer)
    {
        $sites = $customer->sites()->latest()->paginate(20);

        return view('sites.index', compact('customer', 'sites'));
    }

    public function create(Customer $customer)
    {
        return view('sites.create', compact('customer'));
    }

    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $customer->sites()->create(array_merge($validated, [
            'company_id' => session('current_company_id'),
        ]));

        return redirect()->route('customers.sites.index', $customer)->with('success', 'Site added successfully.');
    }

    public function show(Site $site)
    {
        $site->load(['customer', 'installedSerials.product', 'tickets']);

        return view('sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        $site->load('customer');

        return view('sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $site->update($validated);

        return redirect()->route('sites.show', $site)->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site)
    {
        $customerId = $site->customer_id;
        $site->delete();

        return redirect()->route('customers.sites.index', $customerId)->with('success', 'Site deleted successfully.');
    }

    public function getForCustomer($customerId)
    {
        $sites = Site::where('customer_id', $customerId)
            ->where('company_id', session('current_company_id'))
            ->select('id', 'site_name', 'address')
            ->get();

        return response()->json($sites);
    }
}
