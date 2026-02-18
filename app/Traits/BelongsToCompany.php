<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        static::creating(function ($model) {
            if (!$model->company_id && session('current_company_id')) {
                $model->company_id = session('current_company_id');
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            if (session('current_company_id')) {
                $builder->where($builder->getModel()->getTable() . '.company_id', session('current_company_id'));
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
