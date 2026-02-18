<?php

namespace App\Helpers;

class CompanyHelper
{
    public static function currentCompanyId()
    {
        return session('current_company_id');
    }

    public static function currentCompany()
    {
        $id = self::currentCompanyId();
        return $id ? \App\Models\Company::find($id) : null;
    }

    public static function userRoleInCompany($user = null)
    {
        $user = $user ?? auth()->user();
        $companyId = self::currentCompanyId();
        if (!$user || !$companyId) return null;

        $pivot = $user->companies()->where('companies.id', $companyId)->first();
        return $pivot ? $pivot->pivot->role : null;
    }
}
