@extends('layouts.app')

@section('title', 'Edit ' . $quotation_template->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Edit Estimate Template</h4>
        <p class="text-muted mb-0">{{ $quotation_template->name }}</p>
    </div>
    <a href="{{ route('estimates.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Estimates
    </a>
</div>

<form action="{{ route('quotation-templates.update', $quotation_template) }}" method="POST" id="templateForm">
    @csrf
    @method('PUT')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Template name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                       value="{{ old('name', $quotation_template->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-list-ul me-1"></i> Line Items</span>
            <button type="button" class="btn btn-sm btn-success" id="addRow">
                <i class="bi bi-plus-lg me-1"></i> Add row
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Product (optional)</th>
                            <th>Description / Name</th>
                            <th class="text-center" style="width: 90px;">Qty</th>
                            <th class="text-end" style="width: 120px;">Unit price</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @foreach($quotation_template->items as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <select class="form-select form-select-sm item-product" name="items[{{ $i }}][product_id]">
                                    <option value="">— Free text —</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm item-desc" name="items[{{ $i }}][description]"
                                       value="{{ old("items.{$i}.description", $item->description) }}" placeholder="Name/description">
                                <input type="hidden" name="items[{{ $i }}][id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $i }}][sort_order]" value="{{ $i }}">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-center" name="items[{{ $i }}][qty]"
                                       value="{{ old("items.{$i}.qty", $item->qty) }}" min="1" step="1">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-end" name="items[{{ $i }}][unit_price]"
                                       value="{{ old("items.{$i}.unit_price", $item->unit_price) }}" min="0" step="0.01">
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Remove"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Save template</button>
        <a href="{{ route('quotation-templates.show', $quotation_template) }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

@endsection

@section('scripts')
<script>
(function() {
    var rowIndex = {{ $quotation_template->items->count() }};
    var products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values());

    function addRow() {
        var tr = document.createElement('tr');
        var options = products.map(function(p) { return '<option value="' + p.id + '">' + p.name + '</option>'; }).join('');
        tr.innerHTML =
            '<td class="text-muted">' + (rowIndex + 1) + '</td>' +
            '<td><select class="form-select form-select-sm item-product" name="items[' + rowIndex + '][product_id]"><option value="">— Free text —</option>' + options + '</select></td>' +
            '<td><input type="text" class="form-control form-control-sm item-desc" name="items[' + rowIndex + '][description]" placeholder="Name/description">' +
            '<input type="hidden" name="items[' + rowIndex + '][id]"><input type="hidden" name="items[' + rowIndex + '][sort_order]" value="' + rowIndex + '"></td>' +
            '<td><input type="number" class="form-control form-control-sm text-center" name="items[' + rowIndex + '][qty]" value="1" min="1" step="1"></td>' +
            '<td><input type="number" class="form-control form-control-sm text-end" name="items[' + rowIndex + '][unit_price]" value="0" min="0" step="0.01"></td>' +
            '<td><button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Remove"><i class="bi bi-trash"></i></button></td>';
        document.getElementById('itemsBody').appendChild(tr);
        rowIndex++;
    }

    document.getElementById('addRow').addEventListener('click', addRow);
    document.getElementById('itemsBody').addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            var tbody = document.getElementById('itemsBody');
            if (tbody.querySelectorAll('tr').length <= 1) return;
            e.target.closest('tr').remove();
        }
    });
})();
</script>
@endsection
