<!DOCTYPE html>
<html dir="ltr">
<head>
    <style>
        .button {
            background-color: #6366f1;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
        }
        .footer { font-size: 12px; color: #777; margin-top: 30px; text-align: center; }
    </style>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;">
        <div style="text-align: right; color: #777;">ProjectFlow Password</div>
        <hr>
        <h2>Hello {{ $name }},</h2>
        <p>You are receiving this email because we received a password reset request for your account.</p>

        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ $url }}" class="button" style="color: white;">Reset Password</a>
        </div>

        <p>If you did not request a password reset, no further action is required.</p>
        <p>Sincerely,<br><strong>ProjectFlow Support</strong></p>

        <div class="footer">
            © 2026 ProjectFlow. All rights reserved.
        </div>
    </div>
</body>
</html>
