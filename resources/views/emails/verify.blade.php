<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #334155; }
        .wrapper { width: 100%; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: #0f172a; padding: 40px; text-align: center; }
        .content { padding: 40px; line-height: 1.6; }
        .footer { padding: 20px 40px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; }
        h1 { margin: 0 0 20px; font-size: 24px; font-weight: 700; color: #1e293b; }
        p { margin: 0 0 20px; }
        .btn { display: inline-block; padding: 14px 32px; background-color: #0f172a; color: #ffffff !important; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 14px; transition: background-color 0.3s; }
        .logo { height: 32px; margin-bottom: 0; }
        .ignore { font-size: 13px; color: #64748b; margin-top: 30px; border-top: 1px dashed #e2e8f0; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <img src="https://res.cloudinary.com/dxto0fp39/image/upload/f_auto,q_auto/Group_2_py1aso" alt="Speedline Automotive" class="logo">
            </div>
            <div class="content">
                <h1>Halo, {{ $user->name }}!</h1>
                <p>Terima kasih telah bergabung dengan <strong>Speedline Automotive</strong>. Kami sangat senang bisa menjadi mitra perawatan kendaraan Anda.</p>
                <p>Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda dan mengaktifkan akun Anda sepenuhnya:</p>
                
                <div style="text-align: center; margin: 40px 0;">
                    <a href="{{ $url }}" class="btn">Verifikasi Email Saya</a>
                </div>

                <p>Tautan ini hanya berlaku selama 60 menit demi keamanan akun Anda.</p>
                
                <div class="ignore">
                    Jika Anda tidak merasa mendaftar di Speedline Automotive, Anda bisa mengabaikan email ini.
                </div>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} Speedline Automotive. Semua Hak Dilindungi.<br>
                Jl. Raya Otomotif No. 123, Jakarta, Indonesia.
            </div>
        </div>
    </div>
</body>
</html>
