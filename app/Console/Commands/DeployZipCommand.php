<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DeployZipCommand extends Command
{
    protected $signature = 'deploy:zip {--output= : Output ZIP path}';
    protected $description = 'Create deployment ZIP with vendor (no server install needed). Upload ZIP, set doc root to public, use /install.';

    /** Paths/filenames to exclude from ZIP (relative to project root). */
    protected array $exclude = [
        '.env',
        '.env.backup',
        '.env.production',
        '.git',
        'node_modules',
        'vendor',
        'public/hot',
        'public/storage',
        'storage/logs',
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/pail',
        'build',
        '.idea',
        '.vscode',
        '.fleet',
        '.nova',
        '.phpunit.cache',
        'tests',
        '*.log',
    ];

    public function handle(): int
    {
        $base = base_path();
        $tempDir = $base . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'deploy-' . date('Ymd-His');
        $zipName = 'erp-deploy-' . date('Ymd') . '.zip';
        $zipPath = $base . DIRECTORY_SEPARATOR . ($this->option('output') ?: $zipName);

        $this->info('Creating deploy ZIP (vendor included). No install needed on server.');
        $this->newLine();

        if (!class_exists(\ZipArchive::class)) {
            $this->error('ZipArchive extension is required. Enable it in php.ini.');
            return self::FAILURE;
        }

        $composer = $this->findComposer();
        if (!$composer) {
            $this->error('Composer not found. Install from https://getcomposer.org and ensure it is in PATH.');
            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($tempDir));
        if (File::isDirectory($tempDir)) {
            File::deleteDirectory($tempDir);
        }
        File::makeDirectory($tempDir, 0755, true);

        $this->info('Copying project files (excluding dev/git)...');
        $this->copyWithExclude($base, $tempDir, $base);

        $this->info('Ensuring storage structure...');
        $this->ensureStorageStructure($tempDir);

        $this->info('Running composer install --no-dev in build dir...');
        $process = new Process(
            array_merge($composer, ['install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--ignore-platform-reqs']),
            $tempDir,
            null,
            null,
            300
        );
        $process->run();
        if (!$process->isSuccessful()) {
            $this->error('Composer install failed: ' . $process->getErrorOutput());
            File::deleteDirectory($tempDir);
            return self::FAILURE;
        }

        $this->info('Creating ZIP...');
        $this->createZip($tempDir, $zipPath);

        File::deleteDirectory($tempDir);
        $this->newLine();
        $this->info('Done: ' . $zipPath);
        $this->line('Upload this ZIP to server, extract, set document root to .../public, then open /install.php and /install.');
        return self::SUCCESS;
    }

    protected function findComposer(): ?array
    {
        $paths = [
            base_path() . DIRECTORY_SEPARATOR . 'composer.phar',
            'composer',
            'composer.phar',
        ];
        foreach ($paths as $path) {
            if (strpos($path, 'composer.phar') !== false && is_file($path)) {
                return [PHP_BINARY, $path];
            }
            if ($path === 'composer' || $path === 'composer.phar') {
                $p = new Process([$path, '--version']);
                $p->run();
                if ($p->isSuccessful()) {
                    return [$path];
                }
            }
        }
        return null;
    }

    protected function copyWithExclude(string $from, string $to, string $base): void
    {
        $sep = DIRECTORY_SEPARATOR;
        $from = rtrim($from, $sep);
        $to = rtrim($to, $sep);
        $base = rtrim($base, $sep);

        if (!is_dir($from)) {
            return;
        }

        $items = @scandir($from);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $src = $from . $sep . $item;
            $rel = str_replace($base . $sep, '', $src);
            $rel = str_replace(['/', '\\'], '/', $rel);

            if ($this->shouldExclude($rel, $item)) {
                continue;
            }

            $dest = $to . $sep . $item;
            if (is_dir($src)) {
                File::ensureDirectoryExists($dest);
                $this->copyWithExclude($src, $dest, $base);
            } else {
                File::ensureDirectoryExists(dirname($dest));
                copy($src, $dest);
            }
        }
    }

    protected function shouldExclude(string $relPath, string $name): bool
    {
        $relPath = str_replace('\\', '/', $relPath);
        foreach ($this->exclude as $pattern) {
            $pattern = str_replace('\\', '/', $pattern);
            if ($pattern === $relPath || $name === $pattern) {
                return true;
            }
            if (str_starts_with($relPath, $pattern . '/') || $relPath === $pattern) {
                return true;
            }
            if (fnmatch($pattern, $name)) {
                return true;
            }
        }
        if (preg_match('/\.log$/i', $name)) {
            return true;
        }
        return false;
    }

    protected function ensureStorageStructure(string $tempDir): void
    {
        $dirs = [
            'storage/app/public',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ];
        foreach ($dirs as $dir) {
            $full = $tempDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
            File::ensureDirectoryExists($full);
        }
    }

    protected function createZip(string $sourceDir, string $zipPath): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create ZIP: ' . $zipPath);
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $baseLength = strlen($sourceDir) + 1;
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $path = $file->getRealPath();
                $entry = substr($path, $baseLength);
                $entry = str_replace('\\', '/', $entry);
                $zip->addFile($path, $entry);
            }
        }
        $zip->close();
    }
}
