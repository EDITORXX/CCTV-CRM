<?php
/**
 * Standalone script to create deployment ZIP (no Artisan needed).
 * Run: php create-deploy-zip.php
 * Same result as: php artisan deploy:zip
 */
$base = __DIR__;
if (!is_file($base . '/vendor/autoload.php')) {
    fwrite(STDERR, "Run composer install first. Then run: php create-deploy-zip.php\n");
    exit(1);
}
require $base . '/vendor/autoload.php';
$app = require_once $base . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$exit = \Illuminate\Support\Facades\Artisan::call('deploy:zip');
exit($exit);
