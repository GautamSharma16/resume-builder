<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your OTP</title>
</head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:520px;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 12px;">
                            <h1 style="margin:0;font-size:24px;">Verify your email</h1>
                            <p style="margin:14px 0 0;color:#4b5563;line-height:1.6;">Hi {{ $name }}, use this code to complete your Resume Builder signup.</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 28px;">
                            <div style="font-size:34px;font-weight:700;letter-spacing:8px;background:#ecfdf5;color:#0f766e;border-radius:8px;padding:18px 12px;">{{ $otp }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 28px;color:#6b7280;font-size:14px;line-height:1.6;">
                            This OTP expires in 5 minutes. If you did not request it, you can safely ignore this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
