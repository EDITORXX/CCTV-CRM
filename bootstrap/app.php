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
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company' => \App\Http\Middleware\CompanyMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'installed' => \App\Http\Middleware\EnsureNotInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Never return null — Laravel calls ->send() on the result; null = white screen / "send() on null".
        // Use try-catch and static fallback so we ALWAYS return a Response.
        $exceptions->renderable(function (\Throwable $e, $request) {
            try {
                $code = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException ? $e->getStatusCode() : 500;
                $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                $trace = '';
                if ($code === 500 && function_exists('config') && config('app.debug')) {
                    $trace = '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
                }
                $title = $code === 404 ? 'Page not found' : 'Something went wrong';
                $home = $code === 404 && function_exists('route') ? '<p><a href="/">Go to home</a></p>' : '';
                return response(
                    '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . $title . '</title><style>body{font-family:system-ui;padding:2rem;max-width:600px;margin:0 auto;} h1{color:#c00;} pre{overflow:auto;font-size:12px;} a{color:#0d6efd;}</style></head><body><h1>' . $title . '</h1><p>' . $message . '</p>' . $trace . $home . '</body></html>',
                    $code,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            } catch (\Throwable $e2) {
                return response(
                    '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body><h1>Something went wrong</h1><p>An error occurred. Please try again or contact support.</p><p><a href="/">Go to home</a></p></body></html>',
                    500,
                    ['Content-Type' => 'text/html; charset=utf-8']
                );
            }
        });

        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            if ($response->getStatusCode() !== 404) {
                return $response;
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
            return $response;
        });
    })->create();
