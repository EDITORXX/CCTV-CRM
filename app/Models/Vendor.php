<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'phone', 'email', 'address', 'gstin', 'created_by',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
