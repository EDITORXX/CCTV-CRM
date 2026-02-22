<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Warranty;
use App\Models\Site;
use App\Models\User;

class DummyInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', 'Gold Security')->first();
        if (!$company) return;

        $customer = Customer::where('company_id', $company->id)
            ->where('phone', '9811111111')
            ->first();
        if (!$customer) return;

        $site = Site::where('customer_id', $customer->id)->first();
        $admin = User::whereHas('companies', fn($q) => $q->where('companies.id', $company->id))->first();

        $cpCamera = Product::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'CP Plus 2.4MP Bullet Camera'],
            [
                'category' => 'Camera', 'brand' => 'CP Plus',
                'model_number' => 'CP-USC-TA24L2-V5', 'hsn_sac' => '85258090',
                'unit' => 'pcs', 'warranty_months' => 36,
                'track_serial' => false, 'sale_price' => 1450,
            ]
        );

        $cpDvr = Product::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'CP Plus 4CH DVR'],
            [
                'category' => 'DVR_NVR', 'brand' => 'CP Plus',
                'model_number' => 'CP-UVR-0401F1-IC', 'hsn_sac' => '85219090',
                'unit' => 'pcs', 'warranty_months' => 36,
                'track_serial' => false, 'sale_price' => 3200,
            ]
        );

        $hdd = Product::where('company_id', $company->id)
            ->where('name', 'Seagate 1TB Surveillance HDD')->first();
        if (!$hdd) {
            $hdd = Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'Seagate 1TB Surveillance HDD'],
                [
                    'category' => 'HDD', 'brand' => 'Seagate',
                    'model_number' => 'ST1000VX005', 'hsn_sac' => '84717020',
                    'unit' => 'pcs', 'warranty_months' => 36,
                    'track_serial' => false, 'sale_price' => 3200,
                ]
            );
        }

        $smps = Product::where('company_id', $company->id)
            ->where('name', 'LIKE', '%SMPS%')->first();
        if (!$smps) {
            $smps = Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => '12V 5A SMPS Power Supply'],
                [
                    'category' => 'SMPS', 'brand' => 'Generic', 'hsn_sac' => '85044090',
                    'unit' => 'pcs', 'warranty_months' => 6,
                    'track_serial' => false, 'sale_price' => 350,
                ]
            );
        }

        $bnc = Product::where('company_id', $company->id)
            ->where('name', 'LIKE', '%BNC%')->first();
        if (!$bnc) {
            $bnc = Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'BNC Connector Pack (10pcs)'],
                [
                    'category' => 'Accessories', 'brand' => 'Generic', 'hsn_sac' => '85366990',
                    'unit' => 'pcs', 'warranty_months' => 0,
                    'track_serial' => false, 'sale_price' => 100,
                ]
            );
        }

        $cable = Product::where('company_id', $company->id)
            ->where('name', 'LIKE', '%Coaxial%')->first();
        if (!$cable) {
            $cable = Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'RG6 Coaxial Cable'],
                [
                    'category' => 'Cable', 'brand' => 'Polycab', 'hsn_sac' => '85444990',
                    'unit' => 'meter', 'warranty_months' => 0,
                    'track_serial' => false, 'sale_price' => 12,
                ]
            );
        }

        $hdmi = Product::where('company_id', $company->id)
            ->where('name', 'LIKE', '%HDMI%')->first();
        if (!$hdmi) {
            $hdmi = Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => 'HDMI Cable 2m'],
                [
                    'category' => 'Accessories', 'brand' => 'Generic', 'hsn_sac' => '85444990',
                    'unit' => 'pcs', 'warranty_months' => 0,
                    'track_serial' => false, 'sale_price' => 200,
                ]
            );
        }

        $lastInvoice = Invoice::where('company_id', $company->id)->orderBy('id', 'desc')->first();
        $nextNum = $lastInvoice ? intval(preg_replace('/\D/', '', explode('-', $lastInvoice->invoice_number)[1] ?? '0')) + 1 : 1;
        $prefix = $company->invoice_prefix ?? 'INV';
        $invoiceNumber = $prefix . '-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        $invoiceDate = now()->format('Y-m-d');
        $warrantyMonths = 36;

        $items = [
            ['product' => $cpCamera, 'qty' => 4, 'price' => 1450, 'gst' => 18, 'warranty' => 36],
            ['product' => $cpDvr,    'qty' => 1, 'price' => 3200, 'gst' => 18, 'warranty' => 36],
            ['product' => $hdd,      'qty' => 1, 'price' => 3200, 'gst' => 18, 'warranty' => 36],
            ['product' => $smps,     'qty' => 1, 'price' => 350,  'gst' => 18, 'warranty' => 6],
            ['product' => $bnc,      'qty' => 2, 'price' => 100,  'gst' => 18, 'warranty' => 0],
            ['product' => $cable,    'qty' => 80, 'price' => 12,  'gst' => 18, 'warranty' => 0],
            ['product' => $hdmi,     'qty' => 1, 'price' => 200,  'gst' => 18, 'warranty' => 0],
        ];

        $subtotal = 0;
        $gstAmount = 0;

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site?->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'is_gst' => true,
            'discount' => 0,
            'notes' => '4 Camera Full CCTV Setup - CP Plus with 3 Year Warranty',
            'status' => 'sent',
            'created_by' => $admin?->id,
        ]);

        foreach ($items as $item) {
            $lineSubtotal = $item['qty'] * $item['price'];
            $lineGst = $lineSubtotal * ($item['gst'] / 100);
            $lineTotal = $lineSubtotal + $lineGst;

            $subtotal += $lineSubtotal;
            $gstAmount += $lineGst;

            $invoiceItem = InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product']->id,
                'qty' => $item['qty'],
                'unit_price' => $item['price'],
                'gst_percent' => $item['gst'],
                'discount' => 0,
                'total' => $lineTotal,
                'warranty_months' => $item['warranty'],
            ]);

            if ($item['warranty'] > 0) {
                Warranty::create([
                    'company_id' => $company->id,
                    'invoice_item_id' => $invoiceItem->id,
                    'product_id' => $item['product']->id,
                    'customer_id' => $customer->id,
                    'start_date' => $invoiceDate,
                    'end_date' => date('Y-m-d', strtotime($invoiceDate . " + {$item['warranty']} months")),
                    'status' => 'active',
                ]);
            }
        }

        $grandTotal = $subtotal + $gstAmount;
        $invoice->update([
            'subtotal' => $subtotal,
            'gst_amount' => $gstAmount,
            'total' => $grandTotal,
        ]);
    }
}
