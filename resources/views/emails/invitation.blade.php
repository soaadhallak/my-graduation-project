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
        <div style="text-align: right; color: #777;">BugFlow Invitation</div>
        <hr>
        <h2>Hello ,</h2>
        <p>You have been invited to join {{ $projectName }} with the role of {{ $role }}.</p>

        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ $url }}" class="button" style="color: white;">Accept Invitation</a>
        </div>

        <p>If you did not request this invitation, no further action is required.</p>
        <p>Sincerely,<br><strong>BugFlow Support</strong></p>

        <div class="footer">
            © 2026 BugFlow. All rights reserved.
        </div>
    </div>
</body>
</html>
