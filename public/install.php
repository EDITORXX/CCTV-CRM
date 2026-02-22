<?php
/**
 * One-time setup: creates .env from .env.example if missing, then redirects to /install
 * Visit this URL once (e.g. https://yoursite.com/install.php) before using the web installer.
 */
$base = dirname(__DIR__);
$env = $base . '/.env';
$example = $base . '/.env.example';

if (file_exists($env)) {
    header('Location: /install', true, 302);
    exit;
}

if (!file_exists($example)) {
    http_response_code(500);
    echo 'Missing .env.example. Cannot create .env.';
    exit;
}

$content = file_get_contents($example);
$key = 'base64:' . base64_encode(random_bytes(32));
$content = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $key, $content);
file_put_contents($env, $content);

header('Location: /install', true, 302);
exit;
