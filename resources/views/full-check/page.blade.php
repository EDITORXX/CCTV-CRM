<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Full Check — 403 / Missing Fix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 1rem; max-width: 900px; margin: 0 auto; font-family: system-ui, sans-serif; font-size: 0.95rem; }
        h4 { margin-bottom: 0.5rem; }
        .summary { font-size: 1.1rem; margin-bottom: 1rem; }
        table { background: #fff; }
        table td, table th { vertical-align: middle; }
        .status-ok { color: #198754; font-weight: bold; }
        .status-fail { color: #dc3545; font-weight: bold; }
        .value-cell { word-break: break-all; max-width: 320px; }
        .note-cell { color: #666; font-size: 0.9rem; }
        .header-row { background: #1a1c2e; color: #fff; }
        tr:nth-child(even) { background: #f8f9fa; }
    </style>
</head>
<body>
    <h4>Full check — sab kuch verify</h4>
    <p class="text-muted">Is page ka screenshot bhejo agar 403 ya kuch missing ho. Red rows = fix karna hai.</p>
    <div class="summary">
        <strong>Result:</strong> <span class="{{ $passCount === $totalCount ? 'status-ok' : 'status-fail' }}">{{ $passCount }}/{{ $totalCount }}</span> checks pass
    </div>

    <table class="table table-bordered">
        <thead class="header-row">
            <tr>
                <th style="width:40px">#</th>
                <th style="width:80px">Status</th>
                <th>Check</th>
                <th class="value-cell">Value</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checks as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    @if($c['ok'])
                        <span class="status-ok">OK</span>
                    @else
                        <span class="status-fail">FAIL</span>
                    @endif
                </td>
                <td><strong>{{ $c['name'] }}</strong></td>
                <td class="value-cell"><code style="font-size:0.85rem">{{ $c['value'] }}</code></td>
                <td class="note-cell">{{ $c['note'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="text-muted small mt-3">
        URL: <code>/full-check</code> — Is page ka screenshot bhej do, missing / 403 fix bata denge.
    </p>
</body>
</html>
