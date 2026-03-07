<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDocumentLayout extends Model
{
    protected $fillable = [
        'company_id',
        'document_type',
        'show_signature',
        'show_stamp',
        'layout_mode',
    ];

    protected $casts = [
        'show_signature' => 'boolean',
        'show_stamp' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
