@extends('layouts.app')

@section('title', 'Add Multiple Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Add Multiple Products</h4>
        <p class="text-muted mb-0">Add several products in one go. Fill the rows below and click Save all.</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Products
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('products.bulk-store') }}" method="POST" id="bulkForm">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="bulkProductsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 28px">#</th>
                            <th>Name <span class="text-danger">*</span></th>
                            <th>Category <span class="text-danger">*</span></th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>HSN/SAC</th>
                            <th>Unit <span class="text-danger">*</span></th>
                            <th>Warranty (months)</th>
                            <th>Track serial</th>
                            <th>Sale price</th>
                        </tr>
                    </thead>
                    <tbody id="bulkRows">
                        @php $oldItems = old('items', [ [] ]); @endphp
                        @foreach($oldItems as $idx => $item)
                        <tr data-row="{{ $idx }}">
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td><input type="text" class="form-control form-control-sm" name="items[{{ $idx }}][name]" value="{{ $item['name'] ?? '' }}" placeholder="Product name"></td>
                            <td>
                                <select class="form-select form-select-sm" name="items[{{ $idx }}][category]">
                                    <option value="">Select</option>
                                    <option value="Camera" {{ ($item['category'] ?? '') == 'Camera' ? 'selected' : '' }}>Camera</option>
                                    <option value="DVR_NVR" {{ ($item['category'] ?? '') == 'DVR_NVR' ? 'selected' : '' }}>DVR/NVR</option>
                                    <option value="HDD" {{ ($item['category'] ?? '') == 'HDD' ? 'selected' : '' }}>HDD</option>
                                    <option value="Cable" {{ ($item['category'] ?? '') == 'Cable' ? 'selected' : '' }}>Cable</option>
                                    <option value="SMPS" {{ ($item['category'] ?? '') == 'SMPS' ? 'selected' : '' }}>SMPS</option>
                                    <option value="Accessories" {{ ($item['category'] ?? '') == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                                    <option value="Other" {{ ($item['category'] ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </td>
                            <td><input type="text" class="form-control form-control-sm" name="items[{{ $idx }}][brand]" value="{{ $item['brand'] ?? '' }}"></td>
                            <td><input type="text" class="form-control form-control-sm" name="items[{{ $idx }}][model_number]" value="{{ $item['model_number'] ?? '' }}"></td>
                            <td><input type="text" class="form-control form-control-sm" name="items[{{ $idx }}][hsn_sac]" value="{{ $item['hsn_sac'] ?? '' }}"></td>
                            <td>
                                <select class="form-select form-select-sm" name="items[{{ $idx }}][unit]">
                                    <option value="pcs" {{ ($item['unit'] ?? 'pcs') == 'pcs' ? 'selected' : '' }}>pcs</option>
                                    <option value="meter" {{ ($item['unit'] ?? '') == 'meter' ? 'selected' : '' }}>meter</option>
                                </select>
                            </td>
                            <td><input type="number" class="form-control form-control-sm" name="items[{{ $idx }}][warranty_months]" value="{{ $item['warranty_months'] ?? '' }}" min="0" placeholder="0"></td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="items[{{ $idx }}][track_serial]" value="1" {{ !empty($item['track_serial']) ? 'checked' : '' }}>
                            </td>
                            <td><input type="number" class="form-control form-control-sm" name="items[{{ $idx }}][sale_price]" value="{{ $item['sale_price'] ?? '' }}" min="0" step="0.01" placeholder="0"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-outline-secondary" id="addRowBtn">
                    <i class="bi bi-plus-lg me-1"></i> Add row
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save all
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var rowIndex = {{ count($oldItems) }};

    function addRow() {
        var html = '<tr data-row="' + rowIndex + '">' +
            '<td class="text-center">' + (rowIndex + 1) + '</td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items[' + rowIndex + '][name]" placeholder="Product name"></td>' +
            '<td><select class="form-select form-select-sm" name="items[' + rowIndex + '][category]"><option value="">Select</option><option value="Camera">Camera</option><option value="DVR_NVR">DVR/NVR</option><option value="HDD">HDD</option><option value="Cable">Cable</option><option value="SMPS">SMPS</option><option value="Accessories">Accessories</option><option value="Other">Other</option></select></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items[' + rowIndex + '][brand]"></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items[' + rowIndex + '][model_number]"></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items[' + rowIndex + '][hsn_sac]"></td>' +
            '<td><select class="form-select form-select-sm" name="items[' + rowIndex + '][unit]"><option value="pcs">pcs</option><option value="meter">meter</option></select></td>' +
            '<td><input type="number" class="form-control form-control-sm" name="items[' + rowIndex + '][warranty_months]" min="0" placeholder="0"></td>' +
            '<td class="text-center"><input type="checkbox" class="form-check-input" name="items[' + rowIndex + '][track_serial]" value="1"></td>' +
            '<td><input type="number" class="form-control form-control-sm" name="items[' + rowIndex + '][sale_price]" min="0" step="0.01" placeholder="0"></td>' +
            '</tr>';
        $('#bulkRows').append(html);
        rowIndex++;
    }

    $('#addRowBtn').on('click', addRow);
});
</script>
@endsection
