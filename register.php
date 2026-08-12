<?php
// Set header untuk memastikan halaman tidak di-cache (opsional namun disarankan untuk halaman penutupan)
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran SCTF 2026 Ditutup</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
            border-top: 5px solid #e74c3c;
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
            line-height: 1;
        }
        h1 {
            color: #e74c3c;
            font-size: 24px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="icon">🔒</div>
        <h1>Pendaftaran SCTF 2026 Telah Di Tutup</h1>
        <p>Mohon maaf, waktu pendaftaran untuk event SCTF 2026 sudah berakhir. Terima kasih atas antusiasme dan partisipasi Anda.</p>
    </div>

</body>
</html>