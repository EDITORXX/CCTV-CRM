<?php

namespace App\Http\Controllers;

use App\Services\FcmService;
use App\Models\FcmToken;
use Illuminate\Http\Request;

class FcmTestController extends Controller
{
    public function __construct(
        protected FcmService $fcm
    ) {}

    public function index()
    {
        $configError = $this->fcm->getConfigError();
        $tokenCount = FcmToken::count();
        $tokens = FcmToken::with('user:id,name,email')->get();

        return view('fcm-test.index', [
            'configError' => $configError,
            'tokenCount' => $tokenCount,
            'tokens' => $tokens,
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $result = $this->fcm->sendToAll(
            $request->input('title'),
            $request->input('body')
        );

        return redirect()
            ->route('fcm-test.index')
            ->with('fcm_result', $result);
    }
}
