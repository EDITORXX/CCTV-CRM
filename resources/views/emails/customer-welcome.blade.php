<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
        .header { background: #1a1c2e; color: #fff; padding: 25px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: 1px; }
        .body { padding: 30px; }
        .credentials { background: #f8f9fa; border: 1px solid #e3e6ec; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .credentials p { margin: 5px 0; }
        .credentials strong { display: inline-block; width: 80px; }
        .btn { display: inline-block; background: #4e73df; color: #fff; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; margin-top: 15px; }
        .footer { background: #f8f9fa; padding: 15px 30px; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #e3e6ec; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $companyName }}</h1>
        </div>
        <div class="body">
            <h2>Welcome, {{ $customerName }}!</h2>
            <p>Your customer account has been created. You can now log in to view your invoices, warranties, and raise support tickets.</p>

            <div class="credentials">
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
            </div>

            <p>Please change your password after your first login for security.</p>

            <a href="{{ $loginUrl }}" class="btn">Login to Your Account</a>
        </div>
        <div class="footer">
            <p>This is an automated email from {{ $companyName }}. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
