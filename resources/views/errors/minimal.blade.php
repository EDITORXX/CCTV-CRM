<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f5f5; margin: 0; padding: 2rem; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .box { background: #fff; padding: 1.5rem 2rem; border-radius: 8px; max-width: 560px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h1 { margin: 0 0 .5rem; font-size: 1.25rem; color: #c00; }
        p { margin: 0; color: #333; line-height: 1.5; }
        pre { margin: 1rem 0 0; padding: 1rem; background: #f8f8f8; border-radius: 4px; font-size: 12px; overflow: auto; white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Something went wrong</h1>
        <p>{{ $message ?? 'An error occurred. Please try again or contact support.' }}</p>
        @if(!empty($trace))
        <pre>{{ $trace }}</pre>
        @endif
    </div>
</body>
</html>
