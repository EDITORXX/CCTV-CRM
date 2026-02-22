<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'name',
            'category',
            'brand',
            'model_number',
            'hsn_sac',
            'unit',
            'warranty_months',
            'track_serial',
            'sale_price',
        ];
    }

    public function collection()
    {
        return collect([
            [
                'Sample Camera 4CH',
                'Camera',
                'CP Plus',
                'CP-E4',
                '8528',
                'pcs',
                24,
                1,
                5500.00,
            ],
            [
                'Cable 100m',
                'Cable',
                null,
                null,
                null,
                'meter',
                0,
                0,
                25.50,
            ],
        ]);
    }
}
