<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SerialNumber;
use App\Models\Vendor;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class EstimateController extends Controller
{
    public function index()
    {
        $estimates = Estimate::with(['customer', 'site'])
            ->orderBy('estimate_date', 'desc')
            ->paginate(20);

        return view('estimates.index', compact('estimates'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $company = Company::find(session('current_company_id'));

        $lastEstimate = Estimate::where('company_id', session('current_company_id'))
            ->orderBy('id', 'desc')->first();
        $prefix = ($company->invoice_prefix ?? 'INV') . '-EST';
        $nextNumber = $prefix . '-' . str_pad(
            ($lastEstimate ? intval(preg_replace('/\D/', '', substr($lastEstimate->estimate_number, -5))) + 1 : 1),
            5, '0', STR_PAD_LEFT
        );

        return view('estimates.create', compact('customers', 'products', 'company', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'site_id' => 'nullable|exists:sites,id',
            'estimate_number' => 'required|string',
            'estimate_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:estimate_date',
            'is_gst' => 'boolean',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.warranty_months' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $companyId = session('current_company_id');
            $isGst = $request->boolean('is_gst');
            $subtotal = 0;
            $gstAmount = 0;
            $totalDiscount = $request->discount ?? 0;

            $estimate = Estimate::create([
                'company_id' => $companyId,
                'customer_id' => $request->customer_id,
                'site_id' => $request->site_id,
                'estimate_number' => $request->estimate_number,
                'estimate_date' => $request->estimate_date,
                'valid_until' => $request->valid_until,
                'is_gst' => $isGst,
                'discount' => $totalDiscount,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $gstPercent = $isGst ? ($item['gst_percent'] ?? 0) : 0;
                $itemDiscount = $item['discount'] ?? 0;
                $lineSubtotal = ($item['qty'] * $item['unit_price']) - $itemDiscount;
                $lineGst = $lineSubtotal * ($gstPercent / 100);
                $lineTotal = $lineSubtotal + $lineGst;

                $subtotal += $lineSubtotal;
                $gstAmount += $lineGst;

                $product = Product::find($item['product_id']);

                EstimateItem::create([
                    'estimate_id' => $estimate->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'gst_percent' => $gstPercent,
                    'discount' => $itemDiscount,
                    'total' => $lineTotal,
                    'warranty_months' => $item['warranty_months'] ?? $product->warranty_months,
                ]);
            }

            $grandTotal = ($subtotal + $gstAmount) - $totalDiscount;
            $estimate->update([
                'subtotal' => $subtotal,
                'gst_amount' => $gstAmount,
                'total' => $grandTotal,
            ]);
        });

        return redirect()->route('estimates.index')->with('success', 'Estimate created successfully.');
    }

    public function show(Estimate $estimate)
    {
        $estimate->load(['customer', 'site', 'items.product', 'creator', 'convertedInvoice']);

        $stockInfo = [];
        foreach ($estimate->items as $item) {
            $product = $item->product;
            if ($product->track_serial) {
                $available = SerialNumber::where('product_id', $product->id)
                    ->where('company_id', session('current_company_id'))
                    ->where('status', 'in_stock')
                    ->count();
            } else {
                $purchased = $product->purchaseItems()->sum('qty');
                $sold = $product->invoiceItems()->sum('qty');
                $available = $purchased - $sold;
            }
            $stockInfo[$item->id] = [
                'available' => $available,
                'required' => $item->qty,
                'short' => max(0, $item->qty - $available),
            ];
        }

        $vendors = Vendor::orderBy('name')->get();

        return view('estimates.show', compact('estimate', 'stockInfo', 'vendors'));
    }

    public function edit(Estimate $estimate)
    {
        if ($estimate->isConverted()) {
            return redirect()->route('estimates.show', $estimate)->with('error', 'Cannot edit a converted estimate.');
        }

        $estimate->load(['items.product']);
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('estimates.edit', compact('estimate', 'customers', 'products'));
    }

    public function update(Request $request, Estimate $estimate)
    {
        if ($estimate->isConverted()) {
            return redirect()->route('estimates.show', $estimate)->with('error', 'Cannot update a converted estimate.');
        }

        $request->validate([
            'status' => 'nullable|in:draft,sent,accepted,rejected',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
        ]);

        $estimate->update($request->only(['status', 'notes', 'valid_until']));

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate updated.');
    }

    public function destroy(Estimate $estimate)
    {
        if ($estimate->isConverted()) {
            return redirect()->route('estimates.show', $estimate)->with('error', 'Cannot delete a converted estimate.');
        }

        $estimate->items()->delete();
        $estimate->delete();

        return redirect()->route('estimates.index')->with('success', 'Estimate deleted.');
    }

    public function convertToInvoice(Estimate $estimate)
    {
        if ($estimate->isConverted()) {
            return redirect()->route('estimates.show', $estimate)->with('error', 'Already converted.');
        }

        $estimate->load('items.product');
        $companyId = session('current_company_id');

        $shortItems = [];
        foreach ($estimate->items as $item) {
            $product = $item->product;
            if ($product->track_serial) {
                $available = SerialNumber::where('product_id', $product->id)
                    ->where('company_id', $companyId)
                    ->where('status', 'in_stock')
                    ->count();
            } else {
                $purchased = $product->purchaseItems()->sum('qty');
                $sold = $product->invoiceItems()->sum('qty');
                $available = $purchased - $sold;
            }

            if ($available < $item->qty) {
                $shortItems[] = [
                    'product' => $product->name,
                    'required' => $item->qty,
                    'available' => $available,
                    'short' => $item->qty - $available,
                ];
            }
        }

        if (!empty($shortItems)) {
            $msg = 'Stock shortage: ';
            foreach ($shortItems as $si) {
                $msg .= "{$si['product']} (need {$si['required']}, have {$si['available']}); ";
            }
            return redirect()->route('estimates.show', $estimate)
                ->with('warning', $msg . 'Create a Purchase Order for the missing items first.');
        }

        DB::transaction(function () use ($estimate, $companyId) {
            $company = Company::find($companyId);
            $lastInvoice = Invoice::where('company_id', $companyId)->orderBy('id', 'desc')->first();
            $invoiceNumber = ($company->invoice_prefix ?? 'INV') . '-' . str_pad(
                ($lastInvoice ? intval(preg_replace('/\D/', '', explode('-', $lastInvoice->invoice_number)[1] ?? '0')) + 1 : 1),
                5, '0', STR_PAD_LEFT
            );

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'customer_id' => $estimate->customer_id,
                'site_id' => $estimate->site_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->toDateString(),
                'is_gst' => $estimate->is_gst,
                'subtotal' => $estimate->subtotal,
                'gst_amount' => $estimate->gst_amount,
                'discount' => $estimate->discount,
                'total' => $estimate->total,
                'status' => 'draft',
                'notes' => $estimate->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($estimate->items as $item) {
                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'unit_price' => $item->unit_price,
                    'gst_percent' => $item->gst_percent,
                    'discount' => $item->discount,
                    'total' => $item->total,
                    'warranty_months' => $item->warranty_months,
                ]);

                $product = $item->product;

                if ($item->warranty_months && $item->warranty_months > 0) {
                    $startDate = now()->toDateString();
                    $endDate = date('Y-m-d', strtotime($startDate . " + {$item->warranty_months} months"));

                    Warranty::create([
                        'company_id' => $companyId,
                        'invoice_item_id' => $invoiceItem->id,
                        'product_id' => $item->product_id,
                        'customer_id' => $estimate->customer_id,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => 'active',
                    ]);
                }
            }

            $estimate->update([
                'status' => 'converted',
                'converted_invoice_id' => $invoice->id,
            ]);
        });

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate converted to Invoice successfully.');
    }

    public function createPurchaseOrder(Request $request, Estimate $estimate)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
        ]);

        $estimate->load('items.product');
        $companyId = session('current_company_id');

        $shortItems = [];
        foreach ($estimate->items as $item) {
            $product = $item->product;
            if ($product->track_serial) {
                $available = SerialNumber::where('product_id', $product->id)
                    ->where('company_id', $companyId)
                    ->where('status', 'in_stock')
                    ->count();
            } else {
                $purchased = $product->purchaseItems()->sum('qty');
                $sold = $product->invoiceItems()->sum('qty');
                $available = $purchased - $sold;
            }

            $short = max(0, $item->qty - $available);
            if ($short > 0) {
                $shortItems[] = [
                    'product_id' => $product->id,
                    'qty' => $short,
                    'unit_price' => $product->purchaseItems()->avg('unit_price') ?? $item->unit_price,
                    'gst_percent' => $item->gst_percent,
                ];
            }
        }

        if (empty($shortItems)) {
            return redirect()->route('estimates.show', $estimate)->with('info', 'All items are in stock. No purchase order needed.');
        }

        DB::transaction(function () use ($request, $shortItems, $companyId) {
            $totalAmount = 0;
            $gstAmount = 0;

            $purchase = Purchase::create([
                'company_id' => $companyId,
                'vendor_id' => $request->vendor_id,
                'bill_number' => null,
                'bill_date' => now()->toDateString(),
                'notes' => 'Auto-created from Estimate',
                'created_by' => auth()->id(),
            ]);

            foreach ($shortItems as $si) {
                $lineTotal = $si['qty'] * $si['unit_price'];
                $lineGst = $lineTotal * ($si['gst_percent'] / 100);
                $lineGrandTotal = $lineTotal + $lineGst;
                $totalAmount += $lineGrandTotal;
                $gstAmount += $lineGst;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $si['product_id'],
                    'qty' => $si['qty'],
                    'unit_price' => $si['unit_price'],
                    'gst_percent' => $si['gst_percent'],
                    'total' => $lineGrandTotal,
                ]);
            }

            $purchase->update([
                'total_amount' => $totalAmount,
                'gst_amount' => $gstAmount,
            ]);
        });

        return redirect()->route('estimates.show', $estimate)->with('success', 'Purchase Order created for out-of-stock items.');
    }

    public function pdf(Estimate $estimate)
    {
        $estimate->load(['customer', 'site', 'items.product', 'creator']);
        $company = Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('estimates.pdf', compact('estimate', 'company'));

        return $pdf->stream('estimate-' . $estimate->estimate_number . '.pdf');
    }

    public function download(Estimate $estimate)
    {
        $estimate->load(['customer', 'site', 'items.product', 'creator']);
        $company = Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('estimates.pdf', compact('estimate', 'company'));

        return $pdf->download('estimate-' . $estimate->estimate_number . '.pdf');
    }
}
