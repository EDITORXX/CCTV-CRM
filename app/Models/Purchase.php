<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'vendor_id', 'bill_number', 'bill_date',
        'total_amount', 'gst_amount', 'notes', 'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'total_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
