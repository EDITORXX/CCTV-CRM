<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', 'Gold Security')->first();
        if (!$company) return;

        $user = User::firstOrCreate(
            ['email' => 'customer@goldsecurity.in'],
            [
                'name' => 'Rajesh Customer',
                'phone' => '9811111111',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $user->assignRole('customer');
        $company->users()->syncWithoutDetaching([$user->id => ['role' => 'customer']]);
    }
}
