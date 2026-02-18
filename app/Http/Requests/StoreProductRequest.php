<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:Camera,DVR_NVR,HDD,Cable,SMPS,Accessories,Other',
            'brand' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'hsn_sac' => 'nullable|string|max:20',
            'unit' => 'required|string|in:pcs,meter',
            'warranty_months' => 'nullable|integer|min:0',
            'track_serial' => 'boolean',
            'sale_price' => 'nullable|numeric|min:0',
        ];
    }
}
