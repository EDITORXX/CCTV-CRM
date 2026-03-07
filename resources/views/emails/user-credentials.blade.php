<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Credentials</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                    <tr>
                        <td style="background:#4361ee;padding:24px 30px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:22px;">Your Login Credentials</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;">
                            <p style="font-size:16px;color:#333;margin-top:0;">Hello <strong>{{ $userName }}</strong>,</p>
                            <p style="font-size:14px;color:#555;">Your account credentials are below. Please keep them safe.</p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-radius:6px;margin:20px 0;">
                                <tr>
                                    <td style="padding:16px 20px;border-bottom:1px solid #e9ecef;">
                                        <strong style="color:#666;font-size:13px;">Email / User ID</strong><br>
                                        <span style="font-size:15px;color:#333;">{{ $userEmail }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <strong style="color:#666;font-size:13px;">Password</strong><br>
                                        <code style="font-size:16px;color:#4361ee;background:#e8ecff;padding:4px 10px;border-radius:4px;display:inline-block;margin-top:4px;">{{ $userPassword }}</code>
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align:center;margin:24px 0 10px;">
                                <a href="{{ $loginUrl }}" style="display:inline-block;background:#4361ee;color:#ffffff;text-decoration:none;padding:12px 30px;border-radius:6px;font-size:15px;font-weight:600;">Login Now</a>
                            </p>

                            <p style="font-size:12px;color:#999;margin-top:24px;text-align:center;">
                                We recommend changing your password after first login.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8f9fa;padding:16px 30px;text-align:center;font-size:12px;color:#999;">
                            This is an automated message. Please do not reply.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
