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

        $response = $next($request);
        if ($response === null) {
            return response('<h1>Server Error</h1><p>Invalid response. Please try again.</p>', 500);
        }
        return $response;
    }
}
