<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'address', 'gstin', 'phone', 'email', 'website',
        'logo_path', 'payment_qr_path', 'gst_enabled', 'invoice_prefix', 'warranty_default_months',
    ];

    protected $casts = [
        'gst_enabled' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user')->withPivot('role')->withTimestamps();
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
