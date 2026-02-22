<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Ticket;
use App\Models\Warranty;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = session('current_company_id');

        $role = $user->companies()
            ->where('companies.id', $companyId)
            ->first()
            ->pivot
            ->role ?? 'technician';

        if ($role === 'technician') {
            return $this->technicianDashboard($user);
        }

        if ($role === 'customer') {
            return redirect()->route('portal.dashboard');
        }

        return $this->adminDashboard($companyId);
    }

    private function technicianDashboard($user)
    {
        $assignedTickets = Ticket::whereHas('assignments', function ($q) use ($user) {
            $q->where('technician_id', $user->id);
        })->whereNotIn('status', ['closed', 'resolved'])
          ->with(['customer', 'site'])
          ->latest()
          ->get();

        return view('dashboard.technician', compact('assignedTickets'));
    }

    private function customerDashboard($user, $companyId)
    {
        $customer = \App\Models\Customer::where('company_id', $companyId)
            ->where(function ($q) use ($user) {
                $q->where('email', $user->email)
                  ->orWhere('phone', $user->phone);
            })->first();

        $invoices = $customer ? $customer->invoices()->latest()->take(5)->get() : collect();
        $warranties = $customer ? $customer->warranties()->where('status', 'active')->get() : collect();
        $complaints = $customer ? $customer->tickets()->latest()->take(5)->get() : collect();

        return view('dashboard.customer', compact('customer', 'invoices', 'warranties', 'complaints'));
    }

    private function adminDashboard($companyId)
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todayTickets = Ticket::where('company_id', $companyId)
            ->whereDate('created_at', $today)
            ->count();

        $monthlySales = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$monthStart, $today])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $monthlyPurchases = Purchase::where('company_id', $companyId)
            ->whereBetween('bill_date', [$monthStart, $today])
            ->sum('total_amount');

        // Current stock value: sum of (available qty * purchase unit price) for all products
        $totalStockValue = 0;
        $stockDetails = [];
        $products = Product::where('company_id', $companyId)->get();

        foreach ($products as $product) {
            if ($product->track_serial) {
                $inStockSerials = \App\Models\SerialNumber::where('product_id', $product->id)
                    ->where('company_id', $companyId)
                    ->where('status', 'in_stock')
                    ->get();
                $qty = $inStockSerials->count();
                $avgCost = $product->purchaseItems()->avg('unit_price') ?? 0;
                $value = $qty * $avgCost;
            } else {
                $purchased = $product->purchaseItems()->sum('qty');
                $sold = $product->invoiceItems()->sum('qty');
                $qty = $purchased - $sold;
                $avgCost = $product->purchaseItems()->avg('unit_price') ?? 0;
                $value = $qty * $avgCost;
            }
            $totalStockValue += $value;

            if ($qty > 0) {
                $stockDetails[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'avg_cost' => $avgCost,
                    'value' => $value,
                ];
            }
        }

        // Monthly cost of goods sold (cost price of items sold this month)
        $monthlyCOGS = 0;
        $monthlyInvoices = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$monthStart, $today])
            ->where('status', '!=', 'cancelled')
            ->with('items.product')
            ->get();

        foreach ($monthlyInvoices as $inv) {
            foreach ($inv->items as $item) {
                $avgBuyPrice = $item->product->purchaseItems()->avg('unit_price') ?? 0;
                $monthlyCOGS += $item->qty * $avgBuyPrice;
            }
        }

        $monthlySalesWithoutGst = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$monthStart, $today])
            ->where('status', '!=', 'cancelled')
            ->sum('subtotal');

        $monthlyProfit = $monthlySalesWithoutGst - $monthlyCOGS;

        $lowStockCount = collect($stockDetails)->filter(fn($s) => $s['qty'] <= 5)->count();
        $lowStockProducts = collect($stockDetails)->filter(fn($s) => $s['qty'] <= 5)->take(10);

        $recentTickets = Ticket::where('company_id', $companyId)
            ->with(['customer', 'site'])
            ->latest()
            ->take(10)
            ->get();

        $expiringWarranties = Warranty::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $today->copy()->addDays(30)])
            ->with(['product', 'customer'])
            ->get();

        return view('dashboard.admin', compact(
            'todayTickets',
            'monthlySales',
            'monthlyPurchases',
            'lowStockCount',
            'totalStockValue',
            'stockDetails',
            'monthlyCOGS',
            'monthlyProfit',
            'monthlySalesWithoutGst',
            'lowStockProducts',
            'recentTickets',
            'expiringWarranties'
        ));
    }
}
