<?php

namespace App\Http\Controllers;

use App\Models\SerialNumber;
use Illuminate\Http\Request;

class SerialNumberController extends Controller
{
    public function search(Request $request)
    {
        $results = collect();

        if ($search = $request->input('q')) {
            $results = SerialNumber::where('company_id', session('current_company_id'))
                ->where('serial_number', 'like', "%{$search}%")
                ->with([
                    'product',
                    'purchaseItem.purchase.vendor',
                    'invoiceItem.invoice.customer',
                    'installedSite',
                ])
                ->paginate(20)
                ->withQueryString();
        }

        return view('serials.search', compact('results'));
    }

    public function show(SerialNumber $serial)
    {
        $serial->load([
            'product',
            'purchaseItem.purchase.vendor',
            'invoiceItem.invoice.customer',
            'installedSite',
        ]);

        $warranty = \App\Models\Warranty::where('serial_number_id', $serial->id)->first();

        $serviceHistory = \App\Models\Ticket::where('company_id', session('current_company_id'))
            ->where('site_id', $serial->installed_site_id)
            ->with(['updates'])
            ->latest()
            ->get();

        return view('serials.show', compact('serial', 'warranty', 'serviceHistory'));
    }
}
