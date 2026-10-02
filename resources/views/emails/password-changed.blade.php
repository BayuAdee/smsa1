<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Berhasil Diubah</title>
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
        .success-box {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 14px 16px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
            color: #166534;
        }
        .security-alert {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 14px 16px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 13px;
            color: #92400e;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
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
            <div class="success-box">
                <strong>Pemberitahuan Keamanan</strong><br> Password untuk akun Bidan Desa Anda telah berhasil diperbarui pada <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong>.
            </div>
            
            <p>Semua sesi login sebelumnya telah dikeluarkan secara otomatis demi keamanan akun Anda. Anda sekarang dapat masuk menggunakan password baru Anda.</p>

            <div class="security-alert">
                <strong>Bukan Anda yang melakukan perubahan ini?</strong><br>
                Jika Anda merasa tidak melakukan perubahan password ini, harap segera hubungi administrator atau lakukan reset password darurat.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Sistunting. Hak cipta dilindungi undang-undang.
        </div>
    </div>
</body>
</html>
