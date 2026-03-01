<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = ['Fuel charges', 'Salary', 'Toolkit', 'Other'];

        Company::all()->each(function (Company $company) use ($defaults) {
            foreach ($defaults as $name) {
                ExpenseCategory::withoutGlobalScope('company')->firstOrCreate(
                    ['company_id' => $company->id, 'name' => $name],
                    ['company_id' => $company->id, 'name' => $name]
                );
            }
        });
    }
}
