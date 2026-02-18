<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Site;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with(['customer', 'site'])
            ->orderBy('invoice_date', 'desc')
            ->paginate(20);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $company = \App\Models\Company::find(session('current_company_id'));

        $lastInvoice = Invoice::where('company_id', session('current_company_id'))
            ->orderBy('id', 'desc')->first();
        $nextNumber = $company->invoice_prefix . '-' . str_pad(
            ($lastInvoice ? intval(preg_replace('/\D/', '', explode('-', $lastInvoice->invoice_number)[1] ?? '0')) + 1 : 1),
            5, '0', STR_PAD_LEFT
        );

        return view('invoices.create', compact('customers', 'products', 'company', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'site_id' => 'nullable|exists:sites,id',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
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
            'items.*.serial_ids' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $companyId = session('current_company_id');
            $isGst = $request->boolean('is_gst');
            $subtotal = 0;
            $gstAmount = 0;
            $totalDiscount = $request->discount ?? 0;

            $invoice = Invoice::create([
                'company_id' => $companyId,
                'customer_id' => $request->customer_id,
                'site_id' => $request->site_id,
                'invoice_number' => $request->invoice_number,
                'invoice_date' => $request->invoice_date,
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
                $warrantyMonths = $item['warranty_months'] ?? $product->warranty_months;

                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'gst_percent' => $gstPercent,
                    'discount' => $itemDiscount,
                    'total' => $lineTotal,
                    'warranty_months' => $warrantyMonths,
                ]);

                if (!empty($item['serial_ids'])) {
                    $serialIds = array_filter(explode(',', $item['serial_ids']));
                    SerialNumber::whereIn('id', $serialIds)->update([
                        'status' => 'sold',
                        'invoice_item_id' => $invoiceItem->id,
                        'installed_site_id' => $request->site_id,
                    ]);
                }

                if ($warrantyMonths && $warrantyMonths > 0) {
                    $startDate = $request->invoice_date;
                    $endDate = date('Y-m-d', strtotime($startDate . " + {$warrantyMonths} months"));

                    if ($product->track_serial && !empty($item['serial_ids'])) {
                        $serialIds = array_filter(explode(',', $item['serial_ids']));
                        foreach ($serialIds as $serialId) {
                            Warranty::create([
                                'company_id' => $companyId,
                                'invoice_item_id' => $invoiceItem->id,
                                'serial_number_id' => $serialId,
                                'product_id' => $item['product_id'],
                                'customer_id' => $request->customer_id,
                                'start_date' => $startDate,
                                'end_date' => $endDate,
                                'status' => 'active',
                            ]);
                        }
                    } else {
                        Warranty::create([
                            'company_id' => $companyId,
                            'invoice_item_id' => $invoiceItem->id,
                            'product_id' => $item['product_id'],
                            'customer_id' => $request->customer_id,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'status' => 'active',
                        ]);
                    }
                }
            }

            $grandTotal = ($subtotal + $gstAmount) - $totalDiscount;
            $invoice->update([
                'subtotal' => $subtotal,
                'gst_amount' => $gstAmount,
                'total' => $grandTotal,
            ]);
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'site', 'items.product', 'items.serialNumbers', 'payments', 'creator']);
        $totalPaid = $invoice->payments->sum('amount');
        $balance = $invoice->total - $totalPaid;
        return view('invoices.show', compact('invoice', 'totalPaid', 'balance'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['items.product']);
        $customers = Customer::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('invoices.edit', compact('invoice', 'customers', 'products'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,paid,cancelled',
            'notes' => 'nullable|string',
        ]);
        $invoice->update($request->only(['status', 'notes']));
        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        foreach ($invoice->items as $item) {
            SerialNumber::where('invoice_item_id', $item->id)->update([
                'status' => 'in_stock',
                'invoice_item_id' => null,
                'installed_site_id' => null,
            ]);
        }

        Warranty::where('invoice_item_id', '!=', null)
            ->whereIn('invoice_item_id', $invoice->items->pluck('id'))
            ->delete();

        $invoice->items()->delete();
        $invoice->payments()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'site', 'items.product', 'items.serialNumbers', 'creator']);
        $company = \App\Models\Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'company'));
        return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['customer', 'site', 'items.product', 'items.serialNumbers', 'creator']);
        $company = \App\Models\Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'company'));
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }
}
