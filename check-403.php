<?php
/**
 * 403 FIX - Is file ko domain ke DOCUMENT ROOT folder me rakho.
 * Example: Agar domain public_html/erp/ use kar raha hai, to is file ko
 * public_html/erp/check-403.php rakho. Phir open karo: https://yourdomain.com/check-403.php
 *
 * Ye page batayega: abhi document root kahan hai, aur Laravel ke liye kahan hona chahiye.
 */
header('Content-Type: text/html; charset=utf-8');
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '?';
$suggestedPublic = rtrim($docRoot, '/\\') . DIRECTORY_SEPARATOR . 'public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Fix — Set document root to public</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 1.5rem; max-width: 680px; margin: 0 auto; font-family: system-ui, sans-serif; }
        pre { background: #1a1c2e; color: #e0e0e0; padding: 1rem; border-radius: 0.5rem; font-size: 0.9rem; }
        .step { background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border-left: 4px solid #0d6efd; }
    </style>
</head>
<body>
    <h4 class="mb-3">403 Forbidden — Fix</h4>
    <p class="text-success fw-bold">Agar ye page dikh raha hai, to PHP chal raha hai. Ab document root change karo.</p>

    <div class="step">
        <strong>1. Abhi domain ka document root ye hai:</strong>
        <pre><?php echo htmlspecialchars($docRoot); ?></pre>
    </div>
    <div class="step">
        <strong>2. Laravel ke liye document root ye hona chahiye (<code>public</code> folder):</strong>
        <pre><?php echo htmlspecialchars($suggestedPublic); ?></pre>
    </div>
    <div class="step">
        <strong>3. Hostinger / cPanel me kya karna hai:</strong>
        <ul class="mb-0">
            <li><strong>Hostinger:</strong> hPanel → Domains → apna domain (erp.mapmysecurity.com) → <strong>Document root</strong> change karo. Value set karo: <code>public_html/erp/public</code> (ya jo bhi folder hai uske andar <code>/public</code>).</li>
            <li><strong>cPanel:</strong> Domains → Domains → your domain → Document root → <code>public_html/erp/public</code>.</li>
            <li>Save karo. 2–3 minute wait karke site dubara kholo: <code>https://erp.mapmysecurity.com</code></li>
        </ul>
    </div>
    <p class="text-muted small">Fix ke baad is file ko delete kar do: <code>check-403.php</code></p>
</body>
</html>
