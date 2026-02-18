<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'phone', 'email', 'address', 'gstin', 'created_by',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
