<?php
namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Models\SerialNumber;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function index()
    {
        $warranties = Warranty::with(['product', 'customer', 'invoiceItem.invoice', 'serialNumber'])
            ->orderBy('end_date', 'asc')
            ->paginate(20);
        return view('warranties.index', compact('warranties'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $warranties = collect();

        if ($query) {
            $warranties = Warranty::with(['product', 'customer', 'serialNumber', 'invoiceItem.invoice'])
                ->where(function ($q) use ($query) {
                    $q->whereHas('serialNumber', function ($sq) use ($query) {
                        $sq->where('serial_number', 'like', "%{$query}%");
                    })->orWhereHas('invoiceItem.invoice', function ($iq) use ($query) {
                        $iq->where('invoice_number', 'like', "%{$query}%");
                    })->orWhereHas('customer', function ($cq) use ($query) {
                        $cq->where('name', 'like', "%{$query}%")
                           ->orWhere('phone', 'like', "%{$query}%");
                    });
                })->get();
        }

        return view('warranties.search', compact('warranties', 'query'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $request->validate(['status' => 'required|in:active,expired,replaced,rma']);
        $warranty->update(['status' => $request->status, 'notes' => $request->notes]);
        return back()->with('success', 'Warranty status updated.');
    }
}
