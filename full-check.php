<?php
/**
 * FULL CHECK — Standalone (Laravel zaruri nahi)
 * Is file ko public_html/erp/ me rakho (check-403.php ke saath).
 * Open: https://erp.mapmysecurity.com/full-check.php
 *
 * Jab domain ka document root public_html/erp hai (galat), tab bhi ye chalega.
 * Sab check karke dikhata hai — SS bhejo to 403 fix bata denge.
 */
header('Content-Type: text/html; charset=utf-8');

$base = __DIR__;
$publicPath = $base . '/public';
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '?';
$docRootNorm = rtrim(str_replace('\\', '/', $docRoot), '/');
$publicPathNorm = rtrim(str_replace('\\', '/', $publicPath), '/');
$docRootIsPublic = ($docRootNorm === $publicPathNorm);

$checks = [];

$checks[] = ['Document root (server)', $docRoot, true, 'Ye folder domain use kar raha hai.'];
$checks[] = ['Laravel public path', $publicPath, true, 'Doc root yahi hona chahiye.'];
$checks[] = ['Document root = public folder?', $docRootIsPublic ? 'Yes' : 'No', $docRootIsPublic, $docRootIsPublic ? 'Sahi.' : 'Galat — Hostinger me doc root = public_html/erp/public set karo.'];

$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks[] = ['PHP version', PHP_VERSION, $phpOk, $phpOk ? 'OK' : '8.0+ chahiye.'];

$hasIndex = is_file($publicPath . '/index.php');
$checks[] = ['public/index.php exists', $hasIndex ? 'Yes' : 'No', $hasIndex, $hasIndex ? '' : 'Missing — 403 ka reason.'];

$hasHtaccess = is_file($publicPath . '/.htaccess');
$checks[] = ['public/.htaccess exists', $hasHtaccess ? 'Yes' : 'No', $hasHtaccess, $hasHtaccess ? '' : 'Pretty URLs ke liye chahiye.'];

$hasEnv = is_file($base . '/.env');
$checks[] = ['.env exists', $hasEnv ? 'Yes' : 'No', $hasEnv, $hasEnv ? '' : 'Copy from .env.example ya /install se banao.'];

$storageExists = is_dir($base . '/storage');
$checks[] = ['storage/ exists', $storageExists ? 'Yes' : 'No', $storageExists, $storageExists ? '' : 'Laravel incomplete.'];

$storageWritable = $storageExists && is_writable($base . '/storage');
$checks[] = ['storage/ writable', $storageWritable ? 'Yes' : 'No', $storageWritable, $storageWritable ? '' : 'Permissions 775 karo.'];

$frameworkExists = is_dir($base . '/storage/framework');
$logsExists = is_dir($base . '/storage/logs');
$checks[] = ['storage/framework & logs', ($frameworkExists && $logsExists) ? 'Yes' : 'No', $frameworkExists && $logsExists, ''];

$bootstrapCacheWritable = is_writable($base . '/bootstrap/cache');
$checks[] = ['bootstrap/cache writable', $bootstrapCacheWritable ? 'Yes' : 'No', $bootstrapCacheWritable, $bootstrapCacheWritable ? '' : '775 karo.'];

$vendorExists = is_file($base . '/vendor/autoload.php');
$checks[] = ['vendor/autoload.php', $vendorExists ? 'Yes' : 'No', $vendorExists, $vendorExists ? '' : 'composer install chalao.'];

$passCount = 0;
foreach ($checks as $c) { if ($c[2]) $passCount++; }
$totalCount = count($checks);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Full Check — 403 Fix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 1rem; max-width: 900px; margin: 0 auto; font-family: system-ui, sans-serif; font-size: 0.95rem; }
        h4 { margin-bottom: 0.5rem; }
        .summary { font-size: 1.1rem; margin-bottom: 1rem; }
        table { background: #fff; }
        .status-ok { color: #198754; font-weight: bold; }
        .status-fail { color: #dc3545; font-weight: bold; }
        .value-cell { word-break: break-all; max-width: 300px; font-size: 0.85rem; }
        .header-row { background: #1a1c2e; color: #fff; }
        tr:nth-child(even) { background: #f8f9fa; }
        .fix-box { background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem; }
    </style>
</head>
<body>
    <h4>Full check (standalone)</h4>
    <p class="text-muted">Is page ka screenshot bhejo — red rows = fix karna. Ye file Laravel ke bina chal rahi hai.</p>
    <div class="summary">
        <strong>Result:</strong> <span class="<?php echo $passCount === $totalCount ? 'status-ok' : 'status-fail'; ?>"><?php echo $passCount; ?>/<?php echo $totalCount; ?></span> checks pass
    </div>

    <table class="table table-bordered">
        <thead class="header-row">
            <tr><th style="width:40px">#</th><th style="width:70px">Status</th><th>Check</th><th class="value-cell">Value</th><th>Note</th></tr>
        </thead>
        <tbody>
            <?php foreach ($checks as $i => $c): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><span class="<?php echo $c[2] ? 'status-ok' : 'status-fail'; ?>"><?php echo $c[2] ? 'OK' : 'FAIL'; ?></span></td>
                <td><strong><?php echo htmlspecialchars($c[0]); ?></strong></td>
                <td class="value-cell"><code><?php echo htmlspecialchars($c[1]); ?></code></td>
                <td><?php echo htmlspecialchars($c[3]); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!$docRootIsPublic): ?>
    <div class="fix-box">
        <strong>403 fix (abhi yahi karo):</strong><br>
        Hostinger hPanel → Domains → erp.mapmysecurity.com → <strong>Document root</strong> change karo.<br>
        Abhi: <code><?php echo htmlspecialchars($docRoot); ?></code><br>
        Karna hai: <code><?php echo htmlspecialchars($publicPath); ?></code><br>
        Ya short: <code>public_html/erp/public</code>
    </div>
    <?php endif; ?>

    <p class="text-muted small mt-3">URL: <code>full-check.php</code> — Is file ko <code>public_html/erp/</code> me rakho.</p>
</body>
</html>
