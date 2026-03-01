<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationTemplateController extends Controller
{
    public function index()
    {
        $templates = QuotationTemplate::with('items')
            ->orderBy('name')
            ->get();

        return view('quotation-templates.index', compact('templates'));
    }

    public function show(QuotationTemplate $quotation_template)
    {
        $quotation_template->load(['items.product']);
        $company = Company::find(session('current_company_id'));
        $customers = Customer::orderBy('name')->get();

        return view('quotation-templates.show', compact('quotation_template', 'company', 'customers'));
    }

    public function edit(QuotationTemplate $quotation_template)
    {
        $quotation_template->load(['items.product']);
        $products = Product::orderBy('name')->get();

        return view('quotation-templates.edit', compact('quotation_template', 'products'));
    }

    public function update(Request $request, QuotationTemplate $quotation_template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:quotation_template_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.sort_order' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $quotation_template) {
            $quotation_template->update(['name' => $request->name]);
            if (empty($quotation_template->slug)) {
                $quotation_template->update(['slug' => \Illuminate\Support\Str::slug($quotation_template->name)]);
            }

            $idsToKeep = [];
            foreach ($request->items as $idx => $row) {
                $data = [
                    'product_id' => $row['product_id'] ?? null,
                    'description' => $row['description'] ?? null,
                    'qty' => (int) $row['qty'],
                    'unit_price' => (float) $row['unit_price'],
                    'sort_order' => $idx,
                ];
                if (!empty($row['id'])) {
                    $item = QuotationTemplateItem::where('quotation_template_id', $quotation_template->id)->find($row['id']);
                    if ($item) {
                        $item->update($data);
                        $idsToKeep[] = $item->id;
                    }
                } else {
                    $item = $quotation_template->items()->create($data);
                    $idsToKeep[] = $item->id;
                }
            }
            $quotation_template->items()->whereNotIn('id', $idsToKeep)->delete();
        });

        return redirect()->route('quotation-templates.show', $quotation_template)->with('success', 'Estimate template updated.');
    }

    public function pdf(QuotationTemplate $quotation_template)
    {
        $quotation_template->load(['items.product']);
        $company = Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('quotation-templates.pdf', compact('quotation_template', 'company'));

        return $pdf->stream('estimate-' . $quotation_template->slug . '.pdf');
    }

    public function download(QuotationTemplate $quotation_template)
    {
        $quotation_template->load(['items.product']);
        $company = Company::find(session('current_company_id'));
        $pdf = Pdf::loadView('quotation-templates.pdf', compact('quotation_template', 'company'));

        return $pdf->download('estimate-' . $quotation_template->slug . '.pdf');
    }

    public function toEstimate(Request $request, QuotationTemplate $quotation_template)
    {
        return redirect()->route('estimates.create', ['template_id' => $quotation_template->id]);
    }
}
