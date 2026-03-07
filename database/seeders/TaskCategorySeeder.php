<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\TaskCategory;
use Illuminate\Database\Seeder;

class TaskCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = ['Service', 'Site Visit', 'Tailing'];

        Company::all()->each(function (Company $company) use ($defaults) {
            foreach ($defaults as $name) {
                TaskCategory::withoutGlobalScope('company')->firstOrCreate(
                    ['company_id' => $company->id, 'name' => $name],
                    ['company_id' => $company->id, 'name' => $name, 'is_default' => true]
                );
            }
        });
    }
}
