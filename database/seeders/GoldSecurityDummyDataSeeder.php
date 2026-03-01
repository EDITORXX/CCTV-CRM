<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Site;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class GoldSecurityDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', 'Gold Security')->first();
        if (!$company) {
            $this->command->warn('Gold Security company not found. Run CompanySeeder first.');
            return;
        }

        $companyId = $company->id;
        $createdBy = $company->users()->first()?->id ?? 1;

        $customersData = [
            ['name' => 'ABC Traders', 'phone' => '9811001001', 'email' => 'abc@example.com', 'address' => 'Block A, Nehru Place, Delhi 110019'],
            ['name' => 'XYZ Enterprises', 'phone' => '9811001002', 'email' => 'xyz@example.com', 'address' => 'Sector 18, Noida 201301'],
            ['name' => 'Metro Mall', 'phone' => '9811001003', 'email' => 'metro@example.com', 'address' => 'MG Road, Gurgaon 122002'],
            ['name' => 'Sunrise Apartments', 'phone' => '9811001004', 'email' => 'sunrise@example.com', 'address' => 'Indirapuram, Ghaziabad 201014'],
            ['name' => 'Tech Park Ltd', 'phone' => '9811001005', 'email' => 'techpark@example.com', 'address' => 'DLF Phase 2, Gurgaon 122002'],
            ['name' => 'City Hospital', 'phone' => '9811001006', 'email' => 'cityhospital@example.com', 'address' => 'Saket, New Delhi 110017'],
            ['name' => 'Grand Hotel', 'phone' => '9811001007', 'email' => 'grandhotel@example.com', 'address' => 'Connaught Place, Delhi 110001'],
            ['name' => 'Smart School', 'phone' => '9811001008', 'email' => 'smartschool@example.com', 'address' => 'Greater Noida 201310'],
            ['name' => 'Prime Industries', 'phone' => '9811001009', 'email' => 'prime@example.com', 'address' => 'Bhiwadi, Rajasthan 301019'],
            ['name' => 'Green Valley Society', 'phone' => '9811001010', 'email' => 'greenvalley@example.com', 'address' => 'Sector 50, Noida 201301'],
        ];

        $customers = [];
        foreach ($customersData as $data) {
            $customers[] = Customer::withoutGlobalScope('company')->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'email' => $data['email'],
                ],
                [
                    'company_id' => $companyId,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'created_by' => $createdBy,
                ]
            );
        }

        $complaintTypes = ['No Video', 'HDD Issue', 'Camera Dead', 'DVR Issue', 'Network Issue', 'Other'];
        $priorities = ['low', 'medium', 'high'];
        $statuses = ['open', 'in_progress', 'resolved', 'closed'];

        $sites = [];
        foreach ($customers as $i => $customer) {
            $siteName = $customer->name . ' - Site ' . ($i + 1);
            $sites[] = Site::withoutGlobalScope('company')->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'site_name' => $siteName,
                ],
                [
                    'company_id' => $companyId,
                    'customer_id' => $customer->id,
                    'site_name' => $siteName,
                    'address' => $customer->address,
                    'contact_person' => $customer->name . ' Contact',
                    'contact_phone' => $customer->phone,
                ]
            );
        }

        $lastTicket = Ticket::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();
        $nextNum = $lastTicket ? (intval(preg_replace('/\D/', '', $lastTicket->ticket_number)) + 1) : 1;

        $ticketDescriptions = [
            'CCTV not showing feed in lobby.',
            'DVR keeps restarting. Need check.',
            'Two cameras offline since morning.',
            'Recording stopped. HDD full or failed.',
            'Night vision not working on camera 3.',
            'Network cable fault reported.',
            'DVR display blank. Power issue suspected.',
            'Camera 5 no video - cable or connector.',
            'Remote viewing not working.',
            'All cameras offline - switch or power.',
        ];

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers[$i % count($customers)];
            $site = $sites[$i % count($sites)];
            $ticketNumber = 'TKT-' . str_pad($nextNum + $i, 5, '0', STR_PAD_LEFT);

            Ticket::withoutGlobalScope('company')->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'ticket_number' => $ticketNumber,
                ],
                [
                    'company_id' => $companyId,
                    'ticket_number' => $ticketNumber,
                    'customer_id' => $customer->id,
                    'site_id' => $site->id,
                    'complaint_type' => $complaintTypes[$i % count($complaintTypes)],
                    'description' => $ticketDescriptions[$i],
                    'priority' => $priorities[$i % 3],
                    'status' => $statuses[$i % 4],
                    'created_by' => $createdBy,
                ]
            );
        }

        $this->command->info('Gold Security: 10 customers, 10 sites, and 10 service tickets created.');
    }
}
