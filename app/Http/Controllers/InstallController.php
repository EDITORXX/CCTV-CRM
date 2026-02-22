<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->route('login')->with('info', 'Application already installed.');
        }

        $this->ensureEnvExists();

        return view('install.index', [
            'dbHost' => env('DB_HOST', '127.0.0.1'),
            'dbPort' => env('DB_PORT', '3306'),
            'dbName' => env('DB_DATABASE', ''),
            'dbUser' => env('DB_USERNAME', ''),
            'dbPass' => env('DB_PASSWORD', ''),
            'appUrl' => env('APP_URL', 'https://' . (request()->getHost() ?: 'erp.example.com')),
        ]);
    }

    public function store(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'db_host' => 'required|string|max:255',
            'db_port' => 'nullable|string|max:10',
            'db_database' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
            'app_url' => 'required|url|max:255',
        ]);

        $this->ensureEnvExists();

        try {
            $this->updateEnv([
                'DB_HOST' => $validated['db_host'],
                'DB_PORT' => $validated['db_port'] ?? '3306',
                'DB_DATABASE' => $validated['db_database'],
                'DB_USERNAME' => $validated['db_username'],
                'DB_PASSWORD' => $validated['db_password'],
                'APP_URL' => rtrim($validated['app_url'], '/'),
            ]);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            DB::purge();
            config(['database.connections.mysql.host' => $validated['db_host']]);
            config(['database.connections.mysql.port' => $validated['db_port'] ?? '3306']);
            config(['database.connections.mysql.database' => $validated['db_database']]);
            config(['database.connections.mysql.username' => $validated['db_username']]);
            config(['database.connections.mysql.password' => $validated['db_password']]);
            DB::reconnect();

            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'db_database' => 'Database connection failed: ' . $e->getMessage(),
            ]);
        }

        try {
            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('migrate', ['--force' => true]);

            if (!File::exists(public_path('storage'))) {
                try {
                    Artisan::call('storage:link');
                } catch (\Throwable $e) {
                    // ignore if link exists or fails
                }
            }

            File::put(storage_path('installed'), date('c'));
            Artisan::call('config:cache');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'install' => 'Installation step failed: ' . $e->getMessage(),
            ]);
        }

        return redirect()->route('login')->with('success', 'Installation completed successfully. You can log in now.');
    }

    protected function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }

    protected function ensureEnvExists(): void
    {
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            return;
        }
        if (!File::exists(base_path('.env.example'))) {
            throw new \RuntimeException('.env.example not found.');
        }
        $content = File::get(base_path('.env.example'));
        if (strpos($content, 'APP_KEY=') !== false && preg_match('/APP_KEY=\s*\n/', $content)) {
            $content = preg_replace('/APP_KEY=\s*\n/', 'APP_KEY=base64:' . base64_encode(random_bytes(32)) . "\n", $content);
        }
        File::put($envPath, $content);
    }

    protected function updateEnv(array $values): void
    {
        $path = base_path('.env');
        $content = File::get($path);

        foreach ($values as $key => $value) {
            $value = (string) $value;
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
            if (strpos($content, "{$key}=") !== false) {
                $content = preg_replace(
                    '/^' . preg_quote($key, '/') . '=.*/m',
                    $key . '="' . $escaped . '"',
                    $content
                );
            } else {
                $content .= "\n{$key}=\"{$escaped}\"";
            }
        }

        File::put($path, $content);
    }
}
