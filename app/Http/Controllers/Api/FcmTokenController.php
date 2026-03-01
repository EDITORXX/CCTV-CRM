<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Store or update FCM token for the authenticated user (on explicit "Enable Notifications" action).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        FcmToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => $request->user()->id, 'device_name' => $request->device_name]
        );

        return response()->json(['success' => true]);
    }
}
