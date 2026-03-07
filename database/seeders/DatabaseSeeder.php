<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CompanySeeder::class,
            ExpenseCategorySeeder::class,
            TaskCategorySeeder::class,
            SampleDataSeeder::class,
            GoldSecurityDummyDataSeeder::class,
            QuotationTemplateSeeder::class,
        ]);
    }
}
