<?php
/**
 * SERVER TEST PAGE - 403 / Forbidden debug
 * Upload this file to your DOCUMENT ROOT (the folder your domain points to).
 * Open: https://yourdomain.com/server-test.php
 *
 * If you see this page = PHP is working. Check "Document root" below.
 * If you get 403 on this file too = server permissions or doc root wrong.
 */
header('Content-Type: text/html; charset=utf-8');
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '?';
$currentDir = __DIR__;
$isPublicFolder = (basename(__DIR__) === 'public');
$hasIndexPhp = file_exists(__DIR__ . '/index.php');
$hasHtaccess = file_exists(__DIR__ . '/.htaccess');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Test — 403 Fix Helper</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 1.5rem; max-width: 720px; margin: 0 auto; font-family: system-ui, sans-serif; }
        .ok { color: #0d6efd; } .warn { color: #856404; } .bad { color: #dc3545; }
        pre { background: #f5f5f5; padding: 0.75rem; border-radius: 0.35rem; font-size: 0.9rem; overflow-x: auto; }
        .card { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h4 class="mb-3">Server test (403 fix helper)</h4>

    <div class="card card-body border-success">
        <strong class="ok">If you see this page, PHP is working.</strong><br>
        Your domain is pointing to the folder shown below as "Document root".
    </div>

    <div class="card card-body">
        <strong>Document root (folder your domain uses):</strong>
        <pre><?php echo htmlspecialchars($docRoot); ?></pre>
        <strong>This file is in:</strong>
        <pre><?php echo htmlspecialchars($currentDir); ?></pre>
        <strong>PHP version:</strong> <?php echo PHP_VERSION; ?>
    </div>

    <div class="card card-body">
        <strong>Checks:</strong>
        <ul class="mb-0">
            <li>Folder name is "public"? <?php echo $isPublicFolder ? '<span class="ok">Yes (correct for Laravel)</span>' : '<span class="warn">No — doc root should be the <code>public</code> folder</span>'; ?></li>
            <li>index.php exists? <?php echo $hasIndexPhp ? '<span class="ok">Yes</span>' : '<span class="bad">No</span>'; ?></li>
            <li>.htaccess exists? <?php echo $hasHtaccess ? '<span class="ok">Yes</span>' : '<span class="warn">No (optional but needed for pretty URLs)</span>'; ?></li>
        </ul>
    </div>

    <div class="card card-body border-warning">
        <strong>403 Forbidden — Kaise fix karein:</strong>
        <ol class="mb-0">
            <li><strong>Document root sahi karo:</strong> Hostinger/cPanel me domain ka "Document root" Laravel ke <code>public</code> folder par set karo. Example: <code>public_html/erp/public</code> (matlab project ke andar wala <code>public</code> folder).</li>
            <li><strong>Abhi doc root kya hai?</strong> Agar upar "Folder name is public? No" dikhe, to domain abhi galat folder use kar raha hai. Hostinger → Domains → your domain → Document root change karke <code>.../erp/public</code> karo.</li>
            <li><strong>Permissions:</strong> <code>public</code> folder aur andar ki files 755 (ya 644 for files). Storage folder (Laravel root me) 775.</li>
            <li><strong>index.php:</strong> Isi folder me <code>index.php</code> hona chahiye. Agar nahi hai, to saari Laravel files wapas upload karo aur doc root = <code>public</code> set karo.</li>
        </ol>
    </div>

    <p class="text-muted small">
        Test page: <code>server-test.php</code> — 403 fix ke baad is file ko delete kar sakte ho.
    </p>
</body>
</html>
