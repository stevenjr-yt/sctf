<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Tidak Ditemukan - SCTF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        html, body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        /* Path background sekolah udah dibikin absolute pakai / di depan */
        .hero-bg { background-image: linear-gradient(rgba(17, 40, 90, 0.85), rgba(15, 23, 42, 0.98)), url('/assets/bg-sekolah.png'); background-size: cover; background-position: center; }
        .glass-card { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5); }
    </style>
</head>
<body class="antialiased m-0 p-0 flex flex-col min-h-screen hero-bg justify-center items-center px-4">

    <div class="glass-card rounded-[2rem] p-8 md:p-16 max-w-2xl w-full text-center border-t-4 border-t-yellow-400">
        <div class="flex justify-center mb-8">
            <img src="/assets/sctf.png" alt="SCTF Logo" class="h-24 w-auto drop-shadow-lg opacity-80">
        </div>
        
        <div class="inline-block bg-yellow-400 text-blue-900 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest mb-6 shadow-lg">
            Error 404
        </div>
        
        <h1 class="text-3xl md:text-5xl font-black text-white mb-6 tracking-tighter drop-shadow-lg">
            Halaman Hilang!
        </h1>
        
        <p class="text-lg text-slate-300 font-light leading-relaxed mb-8">
            Maaf, halaman yang Anda cari mungkin telah dipindahkan, dihapus, atau memang tidak pernah ada. Silakan periksa kembali URL Anda.
        </p>
        
        <div class="flex justify-center">
            <a href="/" class="bg-yellow-400 hover:bg-yellow-500 text-blue-900 px-8 py-3 rounded-full font-bold transition shadow-md transform hover:-translate-y-1">
                Kembali ke Beranda
            </a>
        </div>
    </div>

</body>
</html>