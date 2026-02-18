<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

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
}
