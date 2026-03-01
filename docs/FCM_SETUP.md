# FCM (Firebase Cloud Messaging) Setup

This document describes how push notifications are set up and how to test them.

## Required environment variables

Add to `.env` on the server (and locally if testing):

```env
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
FIREBASE_VAPID_KEY=your-vapid-key-from-firebase-console
```

- **FIREBASE_CREDENTIALS**: Path to the Firebase service account JSON file (relative to project root). Used by the backend to send push messages via FCM HTTP v1.
- **FIREBASE_VAPID_KEY**: Web Push key pair from Firebase Console → Project Settings → Cloud Messaging → Web Push certificates → “Generate key pair”. Used by the browser when calling `getToken()`.

## Where to place `firebase-credentials.json` on the server

1. Download the JSON from Firebase Console: **Project Settings** → **Service accounts** → **Generate new private key**.
2. Upload the file to the server at:  
   `storage/app/firebase-credentials.json`  
   (path relative to the Laravel project root, e.g. `/home/.../public_html/erp`).
3. Restrict permissions:  
   `chmod 600 storage/app/firebase-credentials.json`
4. **Do not commit this file.** It is listed in `.gitignore`.

## How the service worker works

- **File**: `public/firebase-messaging-sw.js`
- The browser registers this script when the user clicks **“Enable Notifications”** in the app (user action required).
- It is loaded from the site root: `https://your-domain.com/firebase-messaging-sw.js`.
- It uses Firebase compat scripts via `importScripts()` and handles **background messages** with `onBackgroundMessage()` so notifications can be shown when the tab is in the background or closed.

## How to test

1. **Enable notifications in the app**  
   Log in, open the user menu (top right), click **“Enable Notifications”**. Allow when the browser prompts.

2. **Confirm token is saved**  
   Check the `fcm_tokens` table for a row with your `user_id` and a long `token` string.

3. **Send a test notification from Firebase**  
   - Firebase Console → **Messaging** → **Create your first campaign** / **New campaign** → **Firebase Notification messages**.  
   - Enter title and body, then **Send test message**.  
   - Paste an FCM token from `fcm_tokens.token` and send.  
   - You should see the notification in the browser (and/or OS).

4. **Optional: send from Laravel**  
   Use the FCM HTTP v1 API (or a package like `kreait/laravel-firebase`) with the service account credentials to send to a token from `fcm_tokens`.

## Cache clear after config change

After changing `.env` or Firebase config:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Troubleshooting: token-subscribe-failed / OAuth credential error

If the browser shows **messaging/token-subscribe-failed** with "Request is missing required authentication credential":

1. **Enable FCM Registration API (main fix)**  
   Firebase SDK 6.7+ needs this API for web `getToken()` to work.  
   - Open: [Google Cloud Console → APIs & Services → Library](https://console.cloud.google.com/apis/library)  
   - Select the **same project** as your Firebase app (e.g. "gold-security-695e8").  
   - Search for **"FCM Registration"** or open: [FCM Registration API](https://console.cloud.google.com/apis/library/fcmregistrations.googleapis.com)  
   - Click **Enable**.  
   - Wait a minute, then try "Allow notifications" again.

2. **VAPID key**  
   Firebase Console → Project Settings → Cloud Messaging → Web Push certificates. Set `FIREBASE_VAPID_KEY` in `.env` to exactly that key (no extra characters or suffix like `@tw210`). Run `php artisan config:clear`.

3. **Service worker URL**  
   Open `https://your-domain.com/firebase-messaging-sw.js` in the browser. It must return 200 and the JS content. If it redirects or 404s, ensure the web server serves `public/firebase-messaging-sw.js` for that path.
