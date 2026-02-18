<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'product_id', 'purchase_item_id', 'serial_number',
        'status', 'invoice_item_id', 'installed_site_id', 'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function installedSite()
    {
        return $this->belongsTo(Site::class, 'installed_site_id');
    }
}
