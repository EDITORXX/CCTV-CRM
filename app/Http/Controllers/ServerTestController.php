<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ServerTestController extends Controller
{
    /**
     * Server test page (same as public/server-test.php) so it works via Laravel route
     * when the physical file is missing on server. URL: /server-test or /server-test.php
     */
    public function __invoke(Request $request)
    {
        $docRoot = $request->server('DOCUMENT_ROOT', '?');
        $currentDir = public_path();
        $isPublicFolder = (basename($currentDir) === 'public');
        $hasIndexPhp = file_exists(public_path('index.php'));
        $hasHtaccess = file_exists(public_path('.htaccess'));

        return response()->view('server-test.page', [
            'docRoot' => $docRoot,
            'currentDir' => $currentDir,
            'isPublicFolder' => $isPublicFolder,
            'hasIndexPhp' => $hasIndexPhp,
            'hasHtaccess' => $hasHtaccess,
        ], 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
