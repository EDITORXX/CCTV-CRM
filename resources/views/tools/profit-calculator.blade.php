@extends('layouts.app')

@section('title', 'Profit Calculator')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-calculator me-2 text-primary"></i>Profit Calculator</h4>
        <p class="text-muted mb-0">Estimate profit before creating an invoice or quotation</p>
    </div>
</div>

<div class="row g-4">
    {{-- Calculator Form --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-sliders me-1"></i> Enter Details
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                    <select id="product-select" class="form-select">
                        <option value="">— Select existing product (optional) —</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}"
                                    data-purchase="{{ $p->purchase_price ?? 0 }}"
                                    data-sale="{{ $p->sale_price ?? 0 }}">
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select to auto-fill purchase & sale price</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                    <input type="text" id="product-name" class="form-control" placeholder="e.g. CP Plus 2MP Camera" maxlength="255">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Purchase Price (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" id="purchase-price" class="form-control" placeholder="0.00" min="0" step="0.01" value="0">
                        </div>
                        <small class="text-muted">Cost / Buying price</small>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Sale Price (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" id="sale-price" class="form-control" placeholder="0.00" min="0" step="0.01" value="0">
                        </div>
                        <small class="text-muted">Selling price</small>
                    </div>
                </div>

                {{-- Expense fields --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Extra Expenses (₹)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" id="expenses" class="form-control" placeholder="0.00" min="0" step="0.01" value="0">
                    </div>
                    <small class="text-muted">Labour, travel, material, etc.</small>
                </div>

                <button type="button" onclick="resetForm()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Profit Display --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-graph-up me-1"></i> Estimate Result
            </div>
            <div class="card-body d-flex flex-column justify-content-center">

                {{-- Profit/Loss Big Number --}}
                <div id="profit-box" class="text-center py-5" style="border-radius:16px; background:#f8f9fa;">
                    <div class="text-muted small text-uppercase fw-semibold mb-2">Estimated Profit</div>
                    <div id="profit-value" style="font-size:3rem;font-weight:800;line-height:1;" class="mb-2">—</div>
                    <div id="profit-label" class="small text-muted">Enter values to see profit</div>
                </div>

                {{-- Breakdown --}}
                <div class="row g-3 mt-3">
                    <div class="col-4">
                        <div class="card bg-light border-0 text-center py-3">
                            <div class="text-muted small">Sale Price</div>
                            <div id="display-sale" class="fw-bold fs-5">₹0.00</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-light border-0 text-center py-3">
                            <div class="text-muted small">Purchase Cost</div>
                            <div id="display-purchase" class="fw-bold fs-5 text-danger">₹0.00</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card bg-light border-0 text-center py-3">
                            <div class="text-muted small">Expenses</div>
                            <div id="display-expenses" class="fw-bold fs-5 text-warning">₹0.00</div>
                        </div>
                    </div>
                </div>

                {{-- Profit Margin --}}
                <div class="mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Profit Margin</span>
                        <span id="margin-pct" class="fw-semibold">—</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div id="margin-bar" class="progress-bar" style="width:0%;"></div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mt-3 alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Profit = Sale Price − Purchase Price − Expenses.
                    Select a product above to auto-fill prices from your product list.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var productSelect = document.getElementById('product-select');
    var productName = document.getElementById('product-name');
    var purchaseInput = document.getElementById('purchase-price');
    var saleInput = document.getElementById('sale-price');
    var expensesInput = document.getElementById('expenses');

    // Auto-fill on product select
    productSelect.addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        if (this.value) {
            purchaseInput.value = parseFloat(opt.dataset.purchase).toFixed(2);
            saleInput.value = parseFloat(opt.dataset.sale).toFixed(2);
            if (!productName.value) {
                productName.value = opt.text;
            }
            calculate();
        }
    });

    // Recalculate on any input change
    [purchaseInput, saleInput, expensesInput].forEach(function(el) {
        el.addEventListener('input', calculate);
    });

    // Manual product name override (don't override when product is auto-selected)
    productName.addEventListener('input', function() {
        // nothing special, just for reference
    });

    function calculate() {
        var sale = parseFloat(saleInput.value) || 0;
        var purchase = parseFloat(purchaseInput.value) || 0;
        var expenses = parseFloat(expensesInput.value) || 0;

        var profit = sale - purchase - expenses;
        var profitBox = document.getElementById('profit-box');
        var profitValue = document.getElementById('profit-value');
        var profitLabel = document.getElementById('profit-label');
        var marginPct = document.getElementById('margin-pct');
        var marginBar = document.getElementById('margin-bar');

        document.getElementById('display-sale').textContent = '₹' + sale.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('display-purchase').textContent = '₹' + purchase.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('display-expenses').textContent = '₹' + expenses.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        if (purchase === 0 && sale === 0 && expenses === 0) {
            profitValue.textContent = '—';
            profitLabel.textContent = 'Enter values to see profit';
            profitValue.style.color = '#888';
            profitBox.style.background = '#f8f9fa';
            marginPct.textContent = '—';
            marginBar.style.width = '0%';
            marginBar.className = 'progress-bar';
            return;
        }

        var absProfit = Math.abs(profit);
        var formatted = '₹' + absProfit.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        if (profit > 0) {
            profitValue.textContent = '+' + formatted;
            profitValue.style.color = '#16a34a';
            profitLabel.textContent = 'Profit';
            profitBox.style.background = 'rgba(22, 163, 74, 0.08)';
            marginBar.className = 'progress-bar bg-success';
        } else if (profit < 0) {
            profitValue.textContent = '-' + formatted;
            profitValue.style.color = '#dc2626';
            profitLabel.textContent = 'Loss';
            profitBox.style.background = 'rgba(220, 38, 38, 0.08)';
            marginBar.className = 'progress-bar bg-danger';
        } else {
            profitValue.textContent = '₹0.00';
            profitValue.style.color = '#d97706';
            profitLabel.textContent = 'No Profit No Loss';
            profitBox.style.background = 'rgba(217, 119, 6, 0.08)';
            marginBar.className = 'progress-bar bg-warning';
        }

        // Margin %
        if (sale > 0) {
            var margin = (profit / sale) * 100;
            marginPct.textContent = margin.toFixed(1) + '%';
            var barWidth = Math.min(Math.max(margin + 50, 5), 100);
            marginBar.style.width = barWidth + '%';
        } else {
            marginPct.textContent = '—';
            marginBar.style.width = '0%';
        }
    }

    window.resetForm = function() {
        productSelect.value = '';
        productName.value = '';
        purchaseInput.value = '0';
        saleInput.value = '0';
        expensesInput.value = '0';
        calculate();
    };

    // Initial state
    calculate();
})();
</script>
@endsection
