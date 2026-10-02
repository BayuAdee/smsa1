<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Akun</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 580px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .header p {
            color: #ccfbf1;
            margin: 4px 0 0;
            font-size: 13px;
        }
        .content {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
        }
        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #0d9488;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.2);
        }
        .info-box {
            background-color: #f0fdfa;
            border-left: 4px solid #0d9488;
            padding: 14px 16px;
            border-radius: 4px;
            margin: 24px 0;
            font-size: 13px;
            color: #134e4a;
        }
        .note {
            font-size: 13px;
            color: #64748b;
            margin-top: 24px;
            border-top: 1px solid #f1f5f9;
            padding-top: 16px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .fallback-url {
            word-break: break-all;
            font-size: 12px;
            color: #0d9488;
            background: #f8fafc;
            padding: 10px;
            border-radius: 6px;
            border: 1px dashed #cbd5e1;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistunting</h1>
            <p>Sistem Informasi Monitoring Posyandu & Stunting</p>
        </div>
        <div class="content">
            <div class="greeting">Halo, {{ $user->name }}</div>
            <p>Kami menerima permintaan untuk mereset password akun <strong>Bidan Desa</strong> Anda di Sistunting.</p>
            <p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>
            
            <div class="button-wrapper">
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Reset Password Akun</a>
            </div>

            <div class="info-box">
                <strong>Perhatian:</strong> Link reset password ini hanya berlaku selama <strong>30 menit</strong> dan hanya dapat digunakan satu kali.
            </div>

            <p class="note">Abaikan email ini jika Anda tidak meminta reset password. Akun Anda tetap aman dan tidak ada perubahan yang dibuat.</p>

            <div style="margin-top: 20px;">
                <p style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Jika tombol di atas tidak dapat diklik, salin dan tempel URL berikut ke browser Anda:</p>
                <div class="fallback-url">{{ $resetUrl }}</div>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Sistunting. Hak cipta dilindungi undang-undang.
        </div>
    </div>
</body>
</html>
