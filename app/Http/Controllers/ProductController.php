<?php

namespace App\Http\Controllers;

use App\Exports\ProductTemplateExport;
use App\Http\Requests\StoreProductRequest;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('company_id', session('current_company_id'));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('model_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $products = $query->latest()->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(StoreProductRequest $request)
    {
        Product::create(array_merge($request->validated(), [
            'company_id' => session('current_company_id'),
            'created_by' => auth()->id(),
        ]));

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['purchaseItems.purchase', 'invoiceItems.invoice', 'serialNumbers']);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function getStock(Product $product)
    {
        if ($product->track_serial) {
            $stock = $product->serialNumbers()->where('status', 'in_stock')->count();
        } else {
            $purchased = $product->purchaseItems()->sum('qty');
            $sold = $product->invoiceItems()->sum('qty');
            $stock = $purchased - $sold;
        }

        return response()->json([
            'product_id' => $product->id,
            'name' => $product->name,
            'stock' => $stock,
            'track_serial' => $product->track_serial,
        ]);
    }

    public function downloadImportTemplate()
    {
        return Excel::download(new ProductTemplateExport(), 'products_import_template.xlsx');
    }

    public function import()
    {
        return view('products.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:5120']);

        try {
            Excel::import(new ProductsImport(), $request->file('file'));
            return redirect()->route('products.index')->with('success', 'Products imported successfully.');
        } catch (ExcelValidationException $e) {
            return redirect()->back()
                ->with('import_errors', $e->errors())
                ->with('import_failures', $e->failures())
                ->withInput();
        }
    }

    public function bulkCreate()
    {
        return view('products.bulk-create');
    }

    public function bulkStore(Request $request)
    {
        $items = $request->input('items', []);
        $validRows = [];
        $errors = [];

        $rules = [
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:Camera,DVR_NVR,HDD,Cable,SMPS,Accessories,Other',
            'brand' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'hsn_sac' => 'nullable|string|max:20',
            'unit' => 'required|string|in:pcs,meter',
            'warranty_months' => 'nullable|integer|min:0',
            'track_serial' => 'nullable',
            'sale_price' => 'nullable|numeric|min:0',
        ];

        foreach ($items as $index => $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }
            $validator = \Illuminate\Support\Facades\Validator::make($row, $rules);
            if ($validator->fails()) {
                $errors['Row ' . ($index + 1)] = $validator->errors()->all();
            } else {
                $validRows[] = $validator->validated();
            }
        }

        if (! empty($errors)) {
            return redirect()->back()->withInput($request->input())->withErrors($errors);
        }

        if (empty($validRows)) {
            return redirect()->back()->with('error', 'No valid product rows to save. Add at least one row with Name, Category and Unit.')->withInput($request->input());
        }

        $companyId = session('current_company_id');
        $createdBy = auth()->id();

        foreach ($validRows as $row) {
            Product::create([
                'company_id' => $companyId,
                'created_by' => $createdBy,
                'name' => $row['name'],
                'category' => $row['category'],
                'brand' => $row['brand'] ?? null,
                'model_number' => $row['model_number'] ?? null,
                'hsn_sac' => $row['hsn_sac'] ?? null,
                'unit' => $row['unit'],
                'warranty_months' => isset($row['warranty_months']) ? (int) $row['warranty_months'] : null,
                'track_serial' => ! empty($row['track_serial']),
                'sale_price' => isset($row['sale_price']) && $row['sale_price'] !== '' ? $row['sale_price'] : null,
            ]);
        }

        return redirect()->route('products.index')->with('success', count($validRows) . ' products created successfully.');
    }

    protected function isEmptyRow(array $row): bool
    {
        $filled = array_filter($row, function ($v) {
            return $v !== null && $v !== '';
        });
        return empty($filled);
    }
}
