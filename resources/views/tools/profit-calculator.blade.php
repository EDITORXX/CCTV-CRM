@extends('layouts.app')

@section('title', 'Profit Calculator')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-calculator me-2 text-primary"></i>Profit Calculator</h4>
        <p class="text-muted mb-0">Estimate profit before creating an invoice or quotation</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" onclick="calculate()" class="btn btn-primary">
            <i class="bi bi-arrow-repeat me-1"></i> Calculate
        </button>
        <button type="button" onclick="resetAll()" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i> Reset
        </button>
    </div>
</div>

{{-- 1-Click Setup Presets --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-lightning me-1"></i> 1-Click Setup
        <span class="small text-muted fw-normal ms-2">— Click to auto-add typical CCTV installation items</span>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @foreach([2, 3, 4, 5, 6] as $cam)
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary" onclick="applyPreset({{ $cam }})">
                        <i class="bi bi-camera-video me-1"></i> {{ $cam }}-Cam Setup
                    </button>
                </div>
            @endforeach
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger" onclick="clearRows()">
                    <i class="bi bi-trash me-1"></i> Clear Items
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Line Items --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="bi bi-list-ul me-1"></i> Items</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted">Product:</span>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="productMode" id="mode-product" value="product" checked onchange="setProductMode('product')">
                        <label class="btn btn-outline-primary btn-sm" for="mode-product">Product List</label>
                        <input type="radio" class="btn-check" name="productMode" id="mode-custom" value="custom" onchange="setProductMode('custom')">
                        <label class="btn btn-outline-primary btn-sm" for="mode-custom">Custom</label>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" onclick="addRow()">
                        <i class="bi bi-plus-lg me-1"></i> Add Item
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="items-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width:32%">Product</th>
                                <th style="width:10%" class="text-center">Qty</th>
                                <th style="width:18%" class="text-end">Purchase (₹)</th>
                                <th style="width:18%" class="text-end">Sale (₹)</th>
                                <th style="width:17%" class="text-end">Total (₹)</th>
                                <th style="width:5%"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            {{-- Rows added by JS --}}
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                        <i class="bi bi-plus-lg me-1"></i> Add Row
                    </button>
                </div>
            </div>
        </div>

        {{-- Extra Expenses --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Extra Expenses (Labour, Travel, etc.)
            </div>
            <div class="card-body">
                <div id="expense-rows">
                    <div class="row g-2 mb-2 expense-row">
                        <div class="col-7">
                            <input type="text" class="form-control form-control-sm exp-desc" placeholder="Description e.g. Labour charge" maxlength="100">
                        </div>
                        <div class="col-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-sm exp-amount" min="0" step="0.01" value="0" placeholder="0.00" oninput="calculate()">
                            </div>
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeExpense(this)"><i class="bi bi-x"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="addExpense()">
                    <i class="bi bi-plus-lg me-1"></i> Add Expense
                </button>
            </div>
        </div>
    </div>

    {{-- Profit Display --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm sticky-top" style="top:20px;">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-graph-up me-1"></i> Estimate Result
            </div>
            <div class="card-body">

                {{-- Big Profit/Loss --}}
                <div id="profit-box" class="text-center py-4 mb-3" style="border-radius:16px; background:#f8f9fa;">
                    <div class="text-muted small text-uppercase fw-semibold mb-2">Estimated Profit</div>
                    <div id="profit-value" style="font-size:2.8rem;font-weight:800;line-height:1;" class="mb-2">₹0.00</div>
                    <div id="profit-label" class="small text-muted">Click "Calculate" to see profit</div>
                </div>

                {{-- Summary Table --}}
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Sale</td>
                        <td class="text-end fw-semibold" id="total-sale">₹0.00</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Purchase</td>
                        <td class="text-end fw-semibold text-danger" id="total-purchase">₹0.00</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Extra Expenses</td>
                        <td class="text-end fw-semibold text-warning" id="total-expenses">₹0.00</td>
                    </tr>
                    <tr class="border-top">
                        <td class="fw-bold">Net Profit</td>
                        <td class="text-end fw-bold fs-5" id="net-profit">₹0.00</td>
                    </tr>
                </table>

                {{-- Margin --}}
                <div class="mt-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Profit Margin</span>
                        <span id="margin-pct" class="fw-semibold">—</span>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div id="margin-bar" class="progress-bar" style="width:0%;"></div>
                    </div>
                </div>

                {{-- Items count --}}
                <div class="mt-3 d-flex gap-3">
                    <div class="flex-fill text-center border rounded-3 py-2">
                        <div class="small text-muted">Items</div>
                        <div class="fw-bold" id="item-count">0</div>
                    </div>
                    <div class="flex-fill text-center border rounded-3 py-2">
                        <div class="small text-muted">Products</div>
                        <div class="fw-bold" id="product-count">0</div>
                    </div>
                    <div class="flex-fill text-center border rounded-3 py-2">
                        <div class="small text-muted">Avg. Margin</div>
                        <div class="fw-bold" id="avg-margin">—</div>
                    </div>
                </div>

                <div class="mt-3 alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Click "Calculate" after entering values. In Total column: if Sale=0, Total shows Purchase amount.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Preset data
var PRESETS = {
    2: [
        { name: 'CCTV Camera (Dome/Bullet)', purchase: 0, sale: 0, qty: 2 },
        { name: 'DVR 4-Channel', purchase: 0, sale: 0, qty: 1 },
        { name: 'HDD 1TB', purchase: 0, sale: 0, qty: 1 },
        { name: 'Cable (2 Core) - 45mtr', purchase: 0, sale: 0, qty: 1 },
        { name: 'BNC Connector', purchase: 0, sale: 0, qty: 4 },
        { name: 'Power Adapter 12V', purchase: 0, sale: 0, qty: 2 },
        { name: 'SMPS 12V 5A', purchase: 0, sale: 0, qty: 1 },
        { name: 'Installation/Service Charge', purchase: 0, sale: 0, qty: 1 },
    ],
    3: [
        { name: 'CCTV Camera (Dome/Bullet)', purchase: 0, sale: 0, qty: 3 },
        { name: 'DVR 4-Channel', purchase: 0, sale: 0, qty: 1 },
        { name: 'HDD 1TB', purchase: 0, sale: 0, qty: 1 },
        { name: 'Cable (2 Core) - 60mtr', purchase: 0, sale: 0, qty: 1 },
        { name: 'BNC Connector', purchase: 0, sale: 0, qty: 6 },
        { name: 'Power Adapter 12V', purchase: 0, sale: 0, qty: 3 },
        { name: 'SMPS 12V 5A', purchase: 0, sale: 0, qty: 1 },
        { name: 'Installation/Service Charge', purchase: 0, sale: 0, qty: 1 },
    ],
    4: [
        { name: 'CCTV Camera (Dome/Bullet)', purchase: 0, sale: 0, qty: 4 },
        { name: 'DVR 4-Channel', purchase: 0, sale: 0, qty: 1 },
        { name: 'HDD 1TB', purchase: 0, sale: 0, qty: 1 },
        { name: 'Cable (2 Core) - 90mtr', purchase: 0, sale: 0, qty: 1 },
        { name: 'BNC Connector', purchase: 0, sale: 0, qty: 8 },
        { name: 'Power Adapter 12V', purchase: 0, sale: 0, qty: 4 },
        { name: 'SMPS 12V 5A', purchase: 0, sale: 0, qty: 1 },
        { name: 'Installation/Service Charge', purchase: 0, sale: 0, qty: 1 },
    ],
    5: [
        { name: 'CCTV Camera (Dome/Bullet)', purchase: 0, sale: 0, qty: 5 },
        { name: 'DVR 8-Channel', purchase: 0, sale: 0, qty: 1 },
        { name: 'HDD 2TB', purchase: 0, sale: 0, qty: 1 },
        { name: 'Cable (2 Core) - 110mtr', purchase: 0, sale: 0, qty: 1 },
        { name: 'BNC Connector', purchase: 0, sale: 0, qty: 10 },
        { name: 'Power Adapter 12V', purchase: 0, sale: 0, qty: 5 },
        { name: 'SMPS 12V 10A', purchase: 0, sale: 0, qty: 1 },
        { name: 'Installation/Service Charge', purchase: 0, sale: 0, qty: 1 },
    ],
    6: [
        { name: 'CCTV Camera (Dome/Bullet)', purchase: 0, sale: 0, qty: 6 },
        { name: 'DVR 8-Channel', purchase: 0, sale: 0, qty: 1 },
        { name: 'HDD 2TB', purchase: 0, sale: 0, qty: 1 },
        { name: 'Cable (2 Core) - 130mtr', purchase: 0, sale: 0, qty: 1 },
        { name: 'BNC Connector', purchase: 0, sale: 0, qty: 12 },
        { name: 'Power Adapter 12V', purchase: 0, sale: 0, qty: 6 },
        { name: 'SMPS 12V 10A', purchase: 0, sale: 0, qty: 1 },
        { name: 'Installation/Service Charge', purchase: 0, sale: 0, qty: 1 },
    ],
};

var rowCounter = 0;
var expCounter = 1;
var currentMode = 'product';
var PRODUCTS_OPTIONS = {!! $productsJson !!};

function getProductOptionsHtml(selectedId) {
    var html = '<option value="">— Select —</option>';
    PRODUCTS_OPTIONS.forEach(function(p) {
        var sel = (p.id == selectedId) ? ' selected' : '';
        html += '<option value="' + p.id + '" data-purchase="' + p.purchase + '" data-sale="' + p.sale + '" data-name="' + p.name.replace(/"/g, '&quot;') + '">' + p.name + '</option>';
    });
    return html;
}

function buildProductCellHtml(mode, selectedId) {
    if (mode === 'custom') {
        return '<input type="text" class="form-control form-control-sm custom-name" placeholder="e.g. Camera, DVR, Labour" maxlength="100">';
    } else {
        return '<select class="form-select form-select-sm product-select" onchange="onProductChange(this)">' +
               getProductOptionsHtml(selectedId) + '</select>';
    }
}

function addRow(productId, purchase, sale, qty) {
    var tbody = document.getElementById('items-body');
    var productCellHtml = buildProductCellHtml(currentMode, productId || '');

    var html = '<tr class="item-row">' +
        '<td class="product-cell">' + productCellHtml + '</td>' +
        '<td><input type="number" class="form-control form-control-sm text-center qty-input" min="1" value="' + (qty || 1) + '"></td>' +
        '<td><input type="number" class="form-control form-control-sm text-end purchase-input" min="0" step="0.01" value="' + (purchase || 0) + '"></td>' +
        '<td><input type="number" class="form-control form-control-sm text-end sale-input" min="0" step="0.01" value="' + (sale || 0) + '"></td>' +
        '<td class="text-end fw-semibold text-muted total-cell">₹0.00</td>' +
        '<td class="text-center">' +
            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-x"></i></button>' +
        '</td>' +
    '</tr>';
    tbody.insertRow().innerHTML = html;
    rowCounter++;
}

function removeRow(btn) {
    var tbody = document.getElementById('items-body');
    if (tbody.rows.length > 1) {
        btn.closest('tr').remove();
    } else {
        // Clear instead of removing last row
        var row = tbody.rows[0];
        row.querySelector('.product-cell').innerHTML = buildProductCellHtml(currentMode, '');
        row.querySelector('.purchase-input').value = 0;
        row.querySelector('.sale-input').value = 0;
        row.querySelector('.qty-input').value = 1;
        row.querySelector('.total-cell').textContent = '₹0.00';
    }
}

function setProductMode(mode) {
    currentMode = mode;
    // Update all existing rows (including the first one)
    var rows = document.querySelectorAll('.item-row');
    rows.forEach(function(row) {
        var productCell = row.querySelector('.product-cell');
        var purchaseVal = row.querySelector('.purchase-input').value;
        var saleVal = row.querySelector('.sale-input').value;
        var qtyVal = row.querySelector('.qty-input').value;

        var selectedId = '';
        if (mode === 'product') {
            // Try to match current custom name to a product
            var customInput = productCell.querySelector('.custom-name');
            if (customInput && customInput.value.trim()) {
                var searchText = customInput.value.toLowerCase();
                PRODUCTS_OPTIONS.forEach(function(p) {
                    if (selectedId === '' && (
                        p.name.toLowerCase().indexOf(searchText) !== -1 ||
                        searchText.indexOf(p.name.toLowerCase()) !== -1
                    )) {
                        selectedId = p.id;
                        purchaseVal = p.purchase || purchaseVal;
                        saleVal = p.sale || saleVal;
                    }
                });
            }
        }

        productCell.innerHTML = buildProductCellHtml(mode, selectedId);
        row.querySelector('.purchase-input').value = purchaseVal || 0;
        row.querySelector('.sale-input').value = saleVal || 0;
        row.querySelector('.qty-input').value = qtyVal || 1;
    });
}

function onProductChange(select) {
    var opt = select.options[select.selectedIndex];
    var row = select.closest('tr');
    if (opt && opt.value) {
        row.querySelector('.purchase-input').value = parseFloat(opt.dataset.purchase || 0).toFixed(2);
        row.querySelector('.sale-input').value = parseFloat(opt.dataset.sale || 0).toFixed(2);
    }
}

function applyPreset(camCount) {
    var preset = PRESETS[camCount];
    if (!preset) return;
    var tbody = document.getElementById('items-body');
    tbody.innerHTML = '';
    rowCounter = 0;
    preset.forEach(function(item) {
        var matchedId = '';
        var purchaseVal = 0;
        var saleVal = 0;
        PRODUCTS_OPTIONS.forEach(function(p) {
            if (
                p.name.toLowerCase().indexOf(item.name.toLowerCase()) !== -1 ||
                item.name.toLowerCase().indexOf(p.name.toLowerCase()) !== -1
            ) {
                matchedId = p.id;
                purchaseVal = p.purchase || 0;
                saleVal = p.sale || 0;
            }
        });
        addRow(matchedId, purchaseVal, saleVal, item.qty);
    });
}

function clearRows() {
    var tbody = document.getElementById('items-body');
    tbody.innerHTML = '';
    rowCounter = 0;
    addRow();
}

function addExpense(desc, amount) {
    var container = document.getElementById('expense-rows');
    var html = '<div class="row g-2 mb-2 expense-row">' +
        '<div class="col-7">' +
            '<input type="text" class="form-control form-control-sm exp-desc" placeholder="Description e.g. Labour charge" maxlength="100" value="' + (desc || '').replace(/"/g, '&quot;') + '">' +
        '</div>' +
        '<div class="col-4">' +
            '<div class="input-group input-group-sm">' +
                '<span class="input-group-text">₹</span>' +
                '<input type="number" class="form-control form-control-sm exp-amount" min="0" step="0.01" value="' + (amount || 0) + '" oninput="calculate()">' +
            '</div>' +
        '</div>' +
        '<div class="col-1">' +
            '<button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeExpense(this)"><i class="bi bi-x"></i></button>' +
        '</div>' +
    '</div>';
    container.insertAdjacentHTML('beforeend', html);
    expCounter++;
}

function removeExpense(btn) {
    var container = document.getElementById('expense-rows');
    if (container.querySelectorAll('.expense-row').length > 1) {
        btn.closest('.expense-row').remove();
    }
}

function calculate() {
    var rows = document.querySelectorAll('.item-row');
    var totalSale = 0;
    var totalPurchase = 0;
    var itemCount = 0;
    var marginSum = 0;
    var marginCount = 0;

    rows.forEach(function(row) {
        var saleInput = row.querySelector('.sale-input');
        var purchaseInput = row.querySelector('.purchase-input');
        var qtyInput = row.querySelector('.qty-input');
        var totalCell = row.querySelector('.total-cell');

        var sale = parseFloat(saleInput.value) || 0;
        var purchase = parseFloat(purchaseInput.value) || 0;
        var qty = parseInt(qtyInput.value) || 1;

        // If sale is 0, show purchase total; otherwise show sale total
        var lineTotal = (sale > 0) ? sale * qty : purchase * qty;
        var linePurchaseTotal = purchase * qty;

        totalSale += lineTotal;
        totalPurchase += linePurchaseTotal;

        // Color coding for total cell
        if (sale > 0) {
            totalCell.textContent = '₹' + lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            totalCell.className = 'text-end fw-semibold text-success total-cell';
        } else if (purchase > 0) {
            totalCell.textContent = '₹' + lineTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            totalCell.className = 'text-end fw-semibold text-muted total-cell';
        } else {
            totalCell.textContent = '₹0.00';
            totalCell.className = 'text-end fw-semibold text-muted total-cell';
        }

        if (sale > 0 || purchase > 0) itemCount++;
        if (sale > 0) {
            marginSum += ((sale - purchase) / sale) * 100;
            marginCount++;
        }
    });

    // Expenses
    var expRows = document.querySelectorAll('.expense-row');
    var totalExpenses = 0;
    expRows.forEach(function(row) {
        var amount = parseFloat(row.querySelector('.exp-amount').value) || 0;
        totalExpenses += amount;
    });

    var profit = totalSale - totalPurchase - totalExpenses;

    // Update summary
    document.getElementById('total-sale').textContent = '₹' + totalSale.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('total-purchase').textContent = '₹' + totalPurchase.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('total-expenses').textContent = '₹' + totalExpenses.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('net-profit').textContent = '₹' + profit.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('item-count').textContent = itemCount;
    document.getElementById('product-count').textContent = itemCount;

    // Big profit box
    var profitBox = document.getElementById('profit-box');
    var profitValue = document.getElementById('profit-value');
    var profitLabel = document.getElementById('profit-label');
    var marginPct = document.getElementById('margin-pct');
    var marginBar = document.getElementById('margin-bar');

    if (itemCount === 0 && totalExpenses === 0) {
        profitValue.textContent = '₹0.00';
        profitLabel.textContent = 'Click "Calculate" to see profit';
        profitValue.style.color = '#888';
        profitBox.style.background = '#f8f9fa';
        marginPct.textContent = '—';
        marginBar.style.width = '0%';
        marginBar.className = 'progress-bar';
        document.getElementById('avg-margin').textContent = '—';
    } else {
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

        if (totalSale > 0) {
            var margin = (profit / totalSale) * 100;
            marginPct.textContent = margin.toFixed(1) + '%';
            var barWidth = Math.min(Math.max((margin + 50), 5), 100);
            marginBar.style.width = barWidth + '%';
        } else {
            marginPct.textContent = '—';
            marginBar.style.width = '0%';
        }

        if (marginCount > 0) {
            document.getElementById('avg-margin').textContent = (marginSum / marginCount).toFixed(1) + '%';
        } else {
            document.getElementById('avg-margin').textContent = '—';
        }
    }
}

function resetAll() {
    var tbody = document.getElementById('items-body');
    tbody.innerHTML = '';
    rowCounter = 0;
    addRow();
    var expContainer = document.getElementById('expense-rows');
    expContainer.innerHTML = '';
    expCounter = 1;
    addExpense();
    // Reset summary
    document.getElementById('total-sale').textContent = '₹0.00';
    document.getElementById('total-purchase').textContent = '₹0.00';
    document.getElementById('total-expenses').textContent = '₹0.00';
    document.getElementById('net-profit').textContent = '₹0.00';
    document.getElementById('item-count').textContent = '0';
    document.getElementById('product-count').textContent = '0';
    document.getElementById('margin-pct').textContent = '—';
    document.getElementById('margin-bar').style.width = '0%';
    document.getElementById('avg-margin').textContent = '—';
    document.getElementById('profit-value').textContent = '₹0.00';
    document.getElementById('profit-value').style.color = '#888';
    document.getElementById('profit-label').textContent = 'Click "Calculate" to see profit';
    document.getElementById('profit-box').style.background = '#f8f9fa';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    addRow();
    addExpense();
});
</script>
@endsection
