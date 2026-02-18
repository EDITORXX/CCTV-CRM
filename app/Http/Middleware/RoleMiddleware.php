<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $companyId = session('current_company_id');
        $user = $request->user();

        if (!$user || !$companyId) {
            abort(403);
        }

        $companyUser = $user->companies()->where('companies.id', $companyId)->first();
        if (!$companyUser) {
            abort(403);
        }

        $userRole = $companyUser->pivot->role;
        if (!in_array($userRole, $roles)) {
            abort(403, 'You do not have the required role to access this page.');
        }

        return $next($request);
    }
}
