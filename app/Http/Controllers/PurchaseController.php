<?php
namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\SerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['vendor', 'items'])
            ->orderBy('bill_date', 'desc')
            ->paginate(20);
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('purchases.create', compact('vendors', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'nullable|string|max:100',
            'bill_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.serials' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $gstAmount = 0;

            $purchase = Purchase::create([
                'company_id' => session('current_company_id'),
                'vendor_id' => $request->vendor_id,
                'bill_number' => $request->bill_number,
                'bill_date' => $request->bill_date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $gstPercent = $item['gst_percent'] ?? 0;
                $lineTotal = $item['qty'] * $item['unit_price'];
                $lineGst = $lineTotal * ($gstPercent / 100);
                $lineGrandTotal = $lineTotal + $lineGst;

                $totalAmount += $lineGrandTotal;
                $gstAmount += $lineGst;

                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'gst_percent' => $gstPercent,
                    'total' => $lineGrandTotal,
                ]);

                if (!empty($item['serials'])) {
                    $serials = array_filter(array_map('trim', explode(',', $item['serials'])));
                    foreach ($serials as $serial) {
                        SerialNumber::create([
                            'company_id' => session('current_company_id'),
                            'product_id' => $item['product_id'],
                            'purchase_item_id' => $purchaseItem->id,
                            'serial_number' => $serial,
                            'status' => 'in_stock',
                        ]);
                    }
                }
            }

            $purchase->update([
                'total_amount' => $totalAmount,
                'gst_amount' => $gstAmount,
            ]);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['vendor', 'items.product', 'items.serialNumbers']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load(['items.product', 'items.serialNumbers']);
        $vendors = Vendor::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('purchases.edit', compact('purchase', 'vendors', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'bill_number' => 'nullable|string|max:100',
            'bill_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $purchase->update($request->only(['vendor_id', 'bill_number', 'bill_date', 'notes']));
        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase updated.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->items->each(function ($item) {
            SerialNumber::where('purchase_item_id', $item->id)->delete();
        });
        $purchase->items()->delete();
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted.');
    }
}
