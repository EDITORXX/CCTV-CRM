<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $companyId;

    protected $createdBy;

    public function __construct()
    {
        $this->companyId = session('current_company_id');
        $this->createdBy = auth()->id();
    }

    public function model(array $row)
    {
        $trackSerial = $this->normalizeTrackSerial($row['track_serial'] ?? null);

        return new Product([
            'company_id' => $this->companyId,
            'created_by' => $this->createdBy,
            'name' => $row['name'] ?? '',
            'category' => $row['category'] ?? '',
            'brand' => $row['brand'] ?? null,
            'model_number' => $row['model_number'] ?? null,
            'hsn_sac' => $row['hsn_sac'] ?? null,
            'unit' => $row['unit'] ?? 'pcs',
            'warranty_months' => $this->numericOrNull($row['warranty_months'] ?? null),
            'track_serial' => $trackSerial,
            'sale_price' => $this->numericOrNull($row['sale_price'] ?? null),
        ]);
    }

    public function rules(): array
    {
        return [
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
    }

    protected function normalizeTrackSerial($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        if (is_numeric($value)) {
            return (int) $value !== 0;
        }
        return in_array(strtolower((string) $value), ['1', 'yes', 'true', 'y'], true);
    }

    protected function numericOrNull($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? $value : null;
    }
}
