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

    /**
     * User ids whose expenses the current user can see.
     * - technician: only own
     * - manager: own + all technicians in company (team)
     * - accountant / company_admin: null = all
     */
    public static function expenseVisibleCreatorIds()
    {
        $role = self::userRoleInCompany();
        if (!$role) return [];

        if (in_array($role, ['accountant', 'company_admin'])) {
            return null; // all
        }

        if ($role === 'technician') {
            return [auth()->id()];
        }

        if ($role === 'manager') {
            $companyId = self::currentCompanyId();
            $technicianIds = \App\Models\Company::find($companyId)
                ->users()
                ->wherePivot('role', 'technician')
                ->pluck('users.id')
                ->toArray();
            return array_values(array_unique(array_merge([auth()->id()], $technicianIds)));
        }

        return [auth()->id()];
    }
}
