<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f6f9; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #000428 0%, #004E92 100%); padding: 36px 40px; text-align: center; }
    .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
    .header p { margin: 6px 0 0; color: rgba(255,255,255,0.65); font-size: 13px; }
    .body { padding: 36px 40px; }
    .body p { margin: 0 0 16px; color: #374151; font-size: 15px; line-height: 1.7; }
    .body .username { font-weight: 600; color: #111827; }
    .btn-wrap { text-align: center; margin: 28px 0; }
    .btn { display: inline-block; background: #004E92; color: #ffffff !important; text-decoration: none; padding: 14px 36px; border-radius: 6px; font-size: 15px; font-weight: 600; letter-spacing: 0.2px; }
    .btn:hover { background: #003d75; }
    .fallback { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; margin: 20px 0 0; }
    .fallback p { margin: 0 0 6px; font-size: 12px; color: #6b7280; }
    .fallback code { word-break: break-all; font-size: 12px; color: #374151; }
    .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 40px; text-align: center; }
    .footer p { margin: 0; font-size: 12px; color: #9ca3af; line-height: 1.6; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>Dashboard One For All</h1>
      <p>Platform Pemantauan Keamanan</p>
    </div>
    <div class="body">
      <p>Halo, <span class="username">{{ $username }}</span></p>
      <p>Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda. Klik tombol di bawah ini untuk membuat kata sandi baru. Tautan ini akan kedaluwarsa dalam <strong>60 menit</strong>.</p>
      <div class="btn-wrap">
        <a href="{{ $resetUrl }}" class="btn">Reset Kata Sandi</a>
      </div>
      <p style="font-size:13px; color:#6b7280;">Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini. Kata sandi Anda tidak akan berubah.</p>
      <div class="fallback">
        <p>Jika tombol di atas tidak berfungsi, salin dan tempel tautan berikut ke browser Anda:</p>
        <code>{{ $resetUrl }}</code>
      </div>
    </div>
    <div class="footer">
      <p>Email ini dikirim oleh Dashboard One For All &bull; Jangan balas email ini</p>
    </div>
  </div>
</body>
</html>
