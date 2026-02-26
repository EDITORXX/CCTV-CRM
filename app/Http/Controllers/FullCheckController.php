<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FullCheckController extends Controller
{
    /**
     * Full server/app check — har cheez verify karo, SS bhejo agar 403 fix karna ho.
     * URL: /full-check
     */
    public function __invoke(Request $request)
    {
        $base = base_path();
        $publicPath = public_path();
        $docRoot = $request->server('DOCUMENT_ROOT', '?');

        $checks = [];

        // 1. Document root
        $checks[] = [
            'name' => 'Document root (server)',
            'value' => $docRoot,
            'ok' => true,
            'note' => 'Ye folder domain use kar raha hai.',
        ];

        // 2. Laravel public path
        $checks[] = [
            'name' => 'Laravel public path',
            'value' => $publicPath,
            'ok' => true,
            'note' => 'Doc root yahi hona chahiye.',
        ];

        // 3. Doc root = public folder?
        $docRootIsPublic = (rtrim(str_replace('\\', '/', $docRoot), '/') === rtrim(str_replace('\\', '/', $publicPath), '/'));
        $checks[] = [
            'name' => 'Document root = public folder?',
            'value' => $docRootIsPublic ? 'Yes' : 'No',
            'ok' => $docRootIsPublic,
            'note' => $docRootIsPublic ? 'Sahi.' : 'Galat — Hostinger me doc root change karke .../public set karo.',
        ];

        // 4. PHP version
        $phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
        $checks[] = [
            'name' => 'PHP version',
            'value' => PHP_VERSION,
            'ok' => $phpOk,
            'note' => $phpOk ? 'OK' : 'Laravel ke liye 8.0+ chahiye.',
        ];

        // 5. index.php in public
        $hasIndex = File::exists($publicPath . '/index.php');
        $checks[] = [
            'name' => 'public/index.php exists',
            'value' => $hasIndex ? 'Yes' : 'No',
            'ok' => $hasIndex,
            'note' => $hasIndex ? '' : 'Missing — 403 ka common reason.',
        ];

        // 6. .htaccess in public
        $hasHtaccess = File::exists($publicPath . '/.htaccess');
        $checks[] = [
            'name' => 'public/.htaccess exists',
            'value' => $hasHtaccess ? 'Yes' : 'No',
            'ok' => $hasHtaccess,
            'note' => $hasHtaccess ? '' : 'Pretty URLs nahi chalenge.',
        ];

        // 7. .env
        $hasEnv = File::exists($base . '/.env');
        $checks[] = [
            'name' => '.env file exists',
            'value' => $hasEnv ? 'Yes' : 'No',
            'ok' => $hasEnv,
            'note' => $hasEnv ? '' : 'Install karte waqt banta hai ya copy from .env.example.',
        ];

        // 8. APP_KEY
        $hasKey = $hasEnv && strlen(config('app.key', '')) > 10;
        $checks[] = [
            'name' => 'APP_KEY set',
            'value' => $hasKey ? 'Yes' : 'No',
            'ok' => $hasKey,
            'note' => $hasKey ? '' : 'php artisan key:generate chalao.',
        ];

        // 9. storage/ exists
        $storageExists = File::isDirectory($base . '/storage');
        $checks[] = [
            'name' => 'storage/ folder exists',
            'value' => $storageExists ? 'Yes' : 'No',
            'ok' => $storageExists,
            'note' => $storageExists ? '' : 'Laravel project incomplete.',
        ];

        // 10. storage/ writable
        $storageWritable = $storageExists && is_writable($base . '/storage');
        $checks[] = [
            'name' => 'storage/ writable',
            'value' => $storageWritable ? 'Yes' : 'No',
            'ok' => $storageWritable,
            'note' => $storageWritable ? '' : 'Permissions 775 karo.',
        ];

        // 11. storage/framework, storage/logs
        $frameworkExists = File::isDirectory($base . '/storage/framework');
        $logsExists = File::isDirectory($base . '/storage/logs');
        $checks[] = [
            'name' => 'storage/framework & storage/logs',
            'value' => ($frameworkExists && $logsExists) ? 'Yes' : 'No',
            'ok' => $frameworkExists && $logsExists,
            'note' => ($frameworkExists && $logsExists) ? '' : 'Ye folders honi chahiye.',
        ];

        // 12. bootstrap/cache writable
        $bootstrapCacheWritable = is_writable($base . '/bootstrap/cache');
        $checks[] = [
            'name' => 'bootstrap/cache writable',
            'value' => $bootstrapCacheWritable ? 'Yes' : 'No',
            'ok' => $bootstrapCacheWritable,
            'note' => $bootstrapCacheWritable ? '' : 'Permissions 775 karo.',
        ];

        // 13. vendor/autoload.php
        $vendorExists = File::exists($base . '/vendor/autoload.php');
        $checks[] = [
            'name' => 'vendor/autoload.php (Composer)',
            'value' => $vendorExists ? 'Yes' : 'No',
            'ok' => $vendorExists,
            'note' => $vendorExists ? '' : 'composer install chalao.',
        ];

        // 14. Installed flag (after web installer)
        $installed = File::exists($base . '/storage/installed');
        $checks[] = [
            'name' => 'App installed (storage/installed)',
            'value' => $installed ? 'Yes' : 'No',
            'ok' => true,
            'note' => $installed ? 'Web installer complete.' : 'Pehli baar /install khol ke install karo.',
        ];

        // 15. Database connection (optional)
        $dbOk = false;
        $dbError = '';
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }
        $checks[] = [
            'name' => 'Database connection',
            'value' => $dbOk ? 'OK' : 'Fail',
            'ok' => $dbOk,
            'note' => $dbOk ? '' : $dbError,
        ];

        // 16. exec() available (storage:link ke liye zaroori on shared hosting)
        $execAvailable = function_exists('exec');
        $checks[] = [
            'name' => 'PHP exec() available',
            'value' => $execAvailable ? 'Yes' : 'No',
            'ok' => $execAvailable,
            'note' => $execAvailable ? 'storage:link chalega.' : 'Disabled — storage link manually banao (File Manager se public/storage → ../storage/app/public).',
        ];

        // 17. public/storage link (uploads/receipts ke liye)
        $storageLinkPath = $publicPath . '/storage';
        $storageLinkExists = File::exists($storageLinkPath);
        $storageLinkOk = $storageLinkExists && (is_link($storageLinkPath) || File::isDirectory($storageLinkPath));
        $checks[] = [
            'name' => 'public/storage link exists',
            'value' => $storageLinkOk ? 'Yes' : 'No',
            'ok' => $storageLinkOk,
            'note' => $storageLinkOk ? 'Uploads/receipts chalenge.' : 'php artisan storage:link chalao ya manually symlink/copy banao.',
        ];

        // 18. Required PHP extensions
        $requiredExt = ['mbstring', 'openssl', 'pdo', 'pdo_mysql', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo'];
        $missingExt = array_filter($requiredExt, fn($e) => !extension_loaded($e));
        $extOk = empty($missingExt);
        $checks[] = [
            'name' => 'PHP extensions (Laravel)',
            'value' => $extOk ? 'All OK' : 'Missing: ' . implode(', ', $missingExt),
            'ok' => $extOk,
            'note' => $extOk ? '' : 'Hostinger PHP options se enable karo.',
        ];

        $passCount = count(array_filter($checks, fn($c) => $c['ok']));
        $totalCount = count($checks);

        return response()->view('full-check.page', [
            'checks' => $checks,
            'passCount' => $passCount,
            'totalCount' => $totalCount,
        ], 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
