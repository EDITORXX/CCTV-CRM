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
            'type'           => 'required|in:product,service',
            'name'           => 'required|string|max:255',
            'category'       => 'required|string|in:Camera,DVR_NVR,HDD,Cable,SMPS,Accessories,IP,Analog,Other,Installation,Repair,Cabling,AMC,Other_Service',
            'brand'          => 'nullable|string|max:255',
            'model_number'   => 'nullable|string|max:255',
            'hsn_sac'        => 'nullable|string|max:20',
            'unit'           => 'nullable|string|in:pcs,meter',
            'warranty_months'=> 'nullable|integer|min:0',
            'track_serial'   => 'boolean',
            'sale_price'     => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
        ];
    }
}
