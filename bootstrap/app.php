<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company' => \App\Http\Middleware\CompanyMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'installed' => \App\Http\Middleware\EnsureNotInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            if ($response->getStatusCode() !== 404) {
                return null;
            }
            if ($request->is('full-check*')) {
                return app(\App\Http\Controllers\FullCheckController::class)($request);
            }
            if ($request->is('server-test*')) {
                $docRoot = $request->server('DOCUMENT_ROOT', '?');
                $currentDir = base_path('public');
                return response()->view('server-test.page', [
                    'docRoot' => $docRoot,
                    'currentDir' => $currentDir,
                    'isPublicFolder' => (basename($currentDir) === 'public'),
                    'hasIndexPhp' => file_exists($currentDir . '/index.php'),
                    'hasHtaccess' => file_exists($currentDir . '/.htaccess'),
                ], 200, ['Content-Type' => 'text/html; charset=utf-8']);
            }
            return null;
        });
    })->create();
