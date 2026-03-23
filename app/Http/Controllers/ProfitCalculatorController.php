<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProfitCalculatorController extends Controller
{
    public function index()
    {
        $products = Product::where('company_id', session('current_company_id'))
            ->where('type', 'product')
            ->orderBy('name')
            ->get(['id', 'name', 'purchase_price', 'sale_price']);

        return view('tools.profit-calculator', compact('products'));
    }
}
