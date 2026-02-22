<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationTestController extends Controller
{
    /**
     * Send a test notification (email). Use for testing mail/notification setup.
     * URL: /notification-test or /notification-test?email=your@email.com
     */
    public function __invoke(Request $request)
    {
        $email = $request->query('email') ?? (auth()->check() ? auth()->user()->email : null);

        if (!$email) {
            return response()->view('notification-test.form', [], 200)
                ->header('Content-Type', 'text/html');
        }

        try {
            Mail::raw(
                "This is a test notification from " . config('app.name') . ".\n\nSent at: " . now()->toDateTimeString() . "\n\nIf you received this, your mail/notification setup is working.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('[' . config('app.name') . '] Test Notification');
                }
            );
            $message = 'Test notification (email) sent successfully to ' . $email . '.';
        } catch (\Throwable $e) {
            $message = 'Failed to send test notification: ' . $e->getMessage();
        }

        return response()->view('notification-test.result', ['message' => $message, 'email' => $email], 200)
            ->header('Content-Type', 'text/html');
    }
}
