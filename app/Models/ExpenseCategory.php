<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = ['company_id', 'name'];

    public function regularExpenses()
    {
        return $this->hasMany(RegularExpense::class, 'expense_category_id');
    }
}
