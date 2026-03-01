<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\QuotationTemplate;
use App\Models\QuotationTemplateItem;
use Illuminate\Database\Seeder;

class QuotationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        if ($companies->isEmpty()) {
            return;
        }

        foreach ($companies as $company) {
            $this->seedTemplatesForCompany($company);
        }
    }

    private function seedTemplatesForCompany(Company $company): void
    {
        $templates = [
            [
                'name' => '4 Cam Setup',
                'slug' => '4-cam-setup',
                'items' => [
                    ['description' => 'IP Camera (4MP)', 'qty' => 4, 'unit_price' => 2500],
                    ['description' => 'DVR/NVR 4 Channel', 'qty' => 1, 'unit_price' => 4500],
                    ['description' => 'HDD 1TB', 'qty' => 1, 'unit_price' => 3500],
                    ['description' => 'Cable & Connectors', 'qty' => 1, 'unit_price' => 1500],
                    ['description' => 'Installation & Configuration', 'qty' => 1, 'unit_price' => 3000],
                ],
            ],
            [
                'name' => '8 Cam Setup',
                'slug' => '8-cam-setup',
                'items' => [
                    ['description' => 'IP Camera (4MP)', 'qty' => 8, 'unit_price' => 2500],
                    ['description' => 'NVR 8 Channel', 'qty' => 1, 'unit_price' => 8500],
                    ['description' => 'HDD 2TB', 'qty' => 1, 'unit_price' => 5500],
                    ['description' => 'Cable & Connectors', 'qty' => 1, 'unit_price' => 2800],
                    ['description' => 'Installation & Configuration', 'qty' => 1, 'unit_price' => 5000],
                ],
            ],
        ];

        foreach ($templates as $tpl) {
            $template = QuotationTemplate::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'slug' => $tpl['slug'],
                ],
                [
                    'name' => $tpl['name'],
                ]
            );

            if ($template->items()->count() > 0) {
                continue;
            }

            foreach ($tpl['items'] as $sortOrder => $item) {
                QuotationTemplateItem::create([
                    'quotation_template_id' => $template->id,
                    'product_id' => null,
                    'description' => $item['description'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }
}
