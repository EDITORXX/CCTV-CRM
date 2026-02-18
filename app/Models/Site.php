<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'customer_id', 'site_name', 'address',
        'contact_person', 'contact_phone', 'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function installedSerials()
    {
        return $this->hasMany(SerialNumber::class, 'installed_site_id');
    }
}
