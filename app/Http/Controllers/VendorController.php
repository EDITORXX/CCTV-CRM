<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::where('company_id', session('current_company_id'));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $vendors = $query->latest()->paginate(20)->withQueryString();

        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(StoreVendorRequest $request)
    {
        Vendor::create(array_merge($request->validated(), [
            'company_id' => session('current_company_id'),
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['purchases' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(StoreVendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        return redirect()->route('vendors.show', $vendor)->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
