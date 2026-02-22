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
        <strong class="ok">If you see this page, PHP &amp; Laravel are working.</strong><br>
        Your domain is pointing to the folder shown below as "Document root".
    </div>

    <div class="card card-body">
        <strong>Document root (folder your domain uses):</strong>
        <pre>{{ $docRoot }}</pre>
        <strong>Laravel public path:</strong>
        <pre>{{ $currentDir }}</pre>
        <strong>PHP version:</strong> {{ PHP_VERSION }}
    </div>

    <div class="card card-body">
        <strong>Checks:</strong>
        <ul class="mb-0">
            <li>Folder name is "public"? @if($isPublicFolder)<span class="ok">Yes (correct for Laravel)</span>@else<span class="warn">No — doc root should be the <code>public</code> folder</span>@endif</li>
            <li>index.php exists? @if($hasIndexPhp)<span class="ok">Yes</span>@else<span class="bad">No</span>@endif</li>
            <li>.htaccess exists? @if($hasHtaccess)<span class="ok">Yes</span>@else<span class="warn">No (optional but needed for pretty URLs)</span>@endif</li>
        </ul>
    </div>

    <div class="card card-body border-warning">
        <strong>403 Forbidden — Kaise fix karein:</strong>
        <ol class="mb-0">
            <li><strong>Document root sahi karo:</strong> Hostinger/cPanel me domain ka "Document root" Laravel ke <code>public</code> folder par set karo. Example: <code>public_html/erp/public</code>.</li>
            <li><strong>Abhi doc root kya hai?</strong> Agar upar "Folder name is public? No" dikhe, to domain abhi galat folder use kar raha hai. Hostinger → Domains → your domain → Document root change karke <code>.../erp/public</code> karo.</li>
            <li><strong>Permissions:</strong> <code>public</code> folder 755. Storage folder (Laravel root me) 775.</li>
        </ol>
    </div>

    <p class="text-muted small">
        Served via Laravel route — no physical file needed.
    </p>
</body>
</html>
