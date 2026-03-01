<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegularExpense extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'expense_category_id', 'amount', 'expense_date',
        'description', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
