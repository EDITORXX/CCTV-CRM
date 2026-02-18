<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Site;
use App\Models\Vendor;
use App\Models\Product;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', 'Gold Security')->first();
        if (!$company) return;

        $vendor1 = Vendor::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Hikvision India Pvt Ltd'],
            ['phone' => '1800123456', 'email' => 'sales@hikvision.in', 'address' => 'Mumbai, Maharashtra', 'gstin' => '27AABCH1234B1Z1']
        );
        $vendor2 = Vendor::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'CP Plus Technology'],
            ['phone' => '1800654321', 'email' => 'info@cpplus.in', 'address' => 'Delhi, India', 'gstin' => '07AABCC1234C1Z2']
        );
        $vendor3 = Vendor::firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Dahua Technology'],
            ['phone' => '1800789012', 'email' => 'support@dahua.in', 'address' => 'Bangalore, Karnataka']
        );

        $products = [
            ['name' => 'Hikvision 2MP Bullet Camera', 'category' => 'Camera', 'brand' => 'Hikvision', 'model_number' => 'DS-2CE1AD0T-IRP', 'hsn_sac' => '85258090', 'unit' => 'pcs', 'warranty_months' => 24, 'track_serial' => true, 'sale_price' => 1800],
            ['name' => 'Hikvision 4MP Dome Camera', 'category' => 'Camera', 'brand' => 'Hikvision', 'model_number' => 'DS-2CD1343G2-I', 'hsn_sac' => '85258090', 'unit' => 'pcs', 'warranty_months' => 24, 'track_serial' => true, 'sale_price' => 3200],
            ['name' => 'Hikvision 8CH DVR', 'category' => 'DVR_NVR', 'brand' => 'Hikvision', 'model_number' => 'DS-7208HUHI-K1', 'hsn_sac' => '85219090', 'unit' => 'pcs', 'warranty_months' => 12, 'track_serial' => true, 'sale_price' => 5500],
            ['name' => 'CP Plus 4CH NVR', 'category' => 'DVR_NVR', 'brand' => 'CP Plus', 'model_number' => 'CP-UNR-4K2041-V3', 'hsn_sac' => '85219090', 'unit' => 'pcs', 'warranty_months' => 12, 'track_serial' => true, 'sale_price' => 4500],
            ['name' => 'Seagate 1TB Surveillance HDD', 'category' => 'HDD', 'brand' => 'Seagate', 'model_number' => 'ST1000VX005', 'hsn_sac' => '84717020', 'unit' => 'pcs', 'warranty_months' => 36, 'track_serial' => true, 'sale_price' => 3200],
            ['name' => 'WD Purple 2TB HDD', 'category' => 'HDD', 'brand' => 'Western Digital', 'model_number' => 'WD20PURZ', 'hsn_sac' => '84717020', 'unit' => 'pcs', 'warranty_months' => 36, 'track_serial' => true, 'sale_price' => 5000],
            ['name' => 'RG6 Coaxial Cable', 'category' => 'Cable', 'brand' => 'Polycab', 'hsn_sac' => '85444990', 'unit' => 'meter', 'warranty_months' => 0, 'track_serial' => false, 'sale_price' => 12],
            ['name' => 'Cat6 UTP Cable', 'category' => 'Cable', 'brand' => 'D-Link', 'hsn_sac' => '85444990', 'unit' => 'meter', 'warranty_months' => 0, 'track_serial' => false, 'sale_price' => 15],
            ['name' => '12V 5A SMPS Power Supply', 'category' => 'SMPS', 'brand' => 'Generic', 'hsn_sac' => '85044090', 'unit' => 'pcs', 'warranty_months' => 6, 'track_serial' => false, 'sale_price' => 350],
            ['name' => 'BNC Connector Pack (10pcs)', 'category' => 'Accessories', 'brand' => 'Generic', 'hsn_sac' => '85366990', 'unit' => 'pcs', 'warranty_months' => 0, 'track_serial' => false, 'sale_price' => 100],
            ['name' => 'HDMI Cable 2m', 'category' => 'Accessories', 'brand' => 'Generic', 'hsn_sac' => '85444990', 'unit' => 'pcs', 'warranty_months' => 0, 'track_serial' => false, 'sale_price' => 200],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['company_id' => $company->id, 'name' => $p['name']],
                array_merge($p, ['company_id' => $company->id])
            );
        }

        $customer1 = Customer::firstOrCreate(
            ['company_id' => $company->id, 'phone' => '9811111111'],
            ['name' => 'Rajesh Kumar', 'email' => 'rajesh@example.com', 'address' => '45, Sector 18, Noida, UP', 'gstin' => '09AABCR1234D1Z3']
        );
        Site::firstOrCreate(
            ['company_id' => $company->id, 'customer_id' => $customer1->id, 'site_name' => 'Rajesh Residence'],
            ['address' => '45, Sector 18, Noida, UP', 'contact_person' => 'Rajesh Kumar', 'contact_phone' => '9811111111']
        );
        Site::firstOrCreate(
            ['company_id' => $company->id, 'customer_id' => $customer1->id, 'site_name' => 'Rajesh Office'],
            ['address' => 'B-12, Sector 63, Noida, UP', 'contact_person' => 'Rajesh Kumar', 'contact_phone' => '9811111111']
        );

        $customer2 = Customer::firstOrCreate(
            ['company_id' => $company->id, 'phone' => '9822222222'],
            ['name' => 'Sonia Sharma', 'email' => 'sonia@example.com', 'address' => '78, Lajpat Nagar, Delhi']
        );
        Site::firstOrCreate(
            ['company_id' => $company->id, 'customer_id' => $customer2->id, 'site_name' => 'Sonia Shop'],
            ['address' => '78, Lajpat Nagar, Delhi', 'contact_person' => 'Sonia Sharma', 'contact_phone' => '9822222222']
        );

        $customer3 = Customer::firstOrCreate(
            ['company_id' => $company->id, 'phone' => '9833333333'],
            ['name' => 'ABC Builders Pvt Ltd', 'email' => 'info@abcbuilders.com', 'address' => 'Connaught Place, Delhi', 'gstin' => '07AABCA1234E1Z5']
        );
        Site::firstOrCreate(
            ['company_id' => $company->id, 'customer_id' => $customer3->id, 'site_name' => 'ABC Tower Site'],
            ['address' => 'Plot 45, Sector 62, Noida', 'contact_person' => 'Site Manager', 'contact_phone' => '9844444444']
        );
        Site::firstOrCreate(
            ['company_id' => $company->id, 'customer_id' => $customer3->id, 'site_name' => 'ABC Mall Project'],
            ['address' => 'Sector 25, Noida', 'contact_person' => 'Project Head', 'contact_phone' => '9855555555']
        );
    }
}
