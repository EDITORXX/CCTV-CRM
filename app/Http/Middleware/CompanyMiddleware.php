<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('current_company_id')) {
            return redirect()->route('company.select');
        }

        $company = \App\Models\Company::find(session('current_company_id'));
        if (!$company) {
            session()->forget('current_company_id');
            return redirect()->route('company.select');
        }

        view()->share('currentCompany', $company);

        $response = $next($request);
        if ($response === null) {
            \Log::error('CompanyMiddleware: pipeline returned null', [
                'path' => $request->path(),
                'url' => $request->fullUrl(),
            ]);
            return response('<h1>Server Error</h1><p>Invalid response. Please try again.</p>', 500);
        }
        return $response;
    }
}
