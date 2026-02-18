<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'Gold Security'],
            [
                'address' => '123, Main Market, Sector 15, Noida, UP - 201301',
                'gstin' => '09AABCG1234A1Z5',
                'phone' => '9876543210',
                'email' => 'info@goldsecurity.in',
                'gst_enabled' => true,
                'invoice_prefix' => 'GS',
                'warranty_default_months' => 12,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@goldsecurity.in'],
            [
                'name' => 'Admin',
                'phone' => '9876543210',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('company_admin');
        $company->users()->syncWithoutDetaching([$admin->id => ['role' => 'company_admin']]);

        $manager = User::firstOrCreate(
            ['email' => 'manager@goldsecurity.in'],
            [
                'name' => 'Rahul Manager',
                'phone' => '9876543211',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');
        $company->users()->syncWithoutDetaching([$manager->id => ['role' => 'manager']]);

        $tech = User::firstOrCreate(
            ['email' => 'tech@goldsecurity.in'],
            [
                'name' => 'Suresh Technician',
                'phone' => '9876543212',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $tech->assignRole('technician');
        $company->users()->syncWithoutDetaching([$tech->id => ['role' => 'technician']]);

        $accountant = User::firstOrCreate(
            ['email' => 'accounts@goldsecurity.in'],
            [
                'name' => 'Priya Accountant',
                'phone' => '9876543213',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $accountant->assignRole('accountant');
        $company->users()->syncWithoutDetaching([$accountant->id => ['role' => 'accountant']]);
    }
}
