<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteExpense extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'site_id', 'customer_id', 'description',
        'amount', 'expense_date', 'technician_name', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
