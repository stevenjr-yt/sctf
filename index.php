<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCTF 2026 | Capture The Flag</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cyber: {
                            500: '#00ffcc',
                            400: '#33ffd6',
                            900: '#0a192f',
                        }
                    }
                }
            }
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        
        .glass {
            background: rgba(10, 25, 47, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 255, 204, 0.2);
        }
        
        .grid-bg {
            background-color: #050b14;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(0, 255, 204, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 255, 204, 0.03) 1px, transparent 1px);
        }

        .cyber-glow {
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.3);
        }

        .accent-border {
            border-left: 4px solid #00ffcc;
        }
    </style>
</head>
<body class="text-white min-h-screen grid-bg">

    <nav class="fixed top-0 w-full z-50 glass border-b border-cyber-500/20 py-4">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <div class="text-2xl font-extrabold tracking-tighter text-cyber-500">SCTF<span class="text-white">2026</span></div>
            <div class="hidden md:flex space-x-8 mono text-sm">
                <a href="#home" class="hover:text-cyber-500 transition">HOME</a>
                <a href="#rules" class="hover:text-cyber-500 transition">RULES</a>
                <a href="#timeline" class="hover:text-cyber-500 transition">TIMELINE</a>
                <a href="register" class="text-cyber-500 border border-cyber-500 px-4 py-1 rounded hover:bg-cyber-500 hover:text-black transition">REGISTER</a>
            </div>
        </div>
    </nav>

    <section id="home" class="pt-40 pb-20 px-6">
        <div class="container mx-auto text-center">
            <h1 class="text-5xl md:text-7xl font-extrabold mb-6 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-500 uppercase">
                Initiate the <br/> <span class="text-cyber-500">Cyber Protocol</span>
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto mb-10">
                Kompetisi Capture The Flag paling bergengsi untuk talenta muda Indonesia. Buktikan kemampuan eksploitasi dan pertahanan sistemmu di SCTF 2026.
            </p>
            <div class="flex flex-col md:flex-row justify-center gap-4">
                <a href="#rules" class="bg-cyber-500 text-black px-8 py-3 rounded-lg font-bold hover:shadow-[0_0_20px_rgba(0,255,204,0.5)] transition uppercase tracking-widest">Aturan & Teknis</a>
                <a href="register" class="glass px-8 py-3 rounded-lg font-bold hover:border-cyber-500 transition uppercase tracking-widest">Join Command</a>
            </div>
        </div>
    </section>

    <section id="rules" class="py-20 px-6 bg-black/40">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-16 text-cyber-500 tracking-widest uppercase">TEKNIS PERLOMBAAN</h2>
            <div class="grid md:grid-cols-2 gap-10">
                
                <div class="glass p-8 rounded-2xl accent-border">
                    <h3 class="text-xl font-bold mb-4 text-cyber-400 mono uppercase tracking-wider">01. Kualifikasi Peserta</h3>
                    <ul class="space-y-3 text-gray-400 text-sm list-none">
                        <li>• Peserta: SMP, SMA/SMK Sederajat, hingga Mahasiswa Aktif (Maksimal Semester 4).</li>
                        <li>• Wajib melampirkan <span class="text-white font-bold">Kartu Pelajar atau KTM</span> asli yang masih berlaku.</li>
                        <li>• Satu tim terdiri dari minimal 1 orang dan maksimal 3 orang.</li>
                    </ul>
                </div>
                
                <div class="glass p-8 rounded-2xl accent-border">
                    <h3 class="text-xl font-bold mb-4 text-cyber-400 mono uppercase tracking-wider">02. AI Policy</h3>
                    <ul class="space-y-3 text-gray-400 text-sm list-none">
                        <li>• <span class="text-white font-bold uppercase">AI INTERFACE: ALLOWED.</span> Peserta diperbolehkan menggunakan Interface AI (Web/Chat).</li>
                        <li>• <span class="text-white font-bold underline italic">MANDATORY CITATION:</span> Jika menggunakan *solver* dari AI, wajib menyertakan <span class="text-white">link AI dan solver</span> yang digunakan!</li>
                        <li>• <span class="text-red-400 font-bold uppercase">NO ONE-SHOT SOLVE.</span> Dilarang keras memasukkan soal secara utuh untuk mendapatkan flag secara instan.</li>
                        <li>• <span class="text-red-400 font-bold uppercase">AGENT AI & CLI: BANNED.</span> Penggunaan Agent otomatis atau alat CLI berbasis AI sangat dilarang.</li>
                    </ul>
                </div>

                <div class="glass p-8 rounded-2xl accent-border">
                    <h3 class="text-xl font-bold mb-4 text-cyber-400 mono uppercase tracking-wider">03. Battle Schedule</h3>
                    <ul class="space-y-3 text-gray-400 text-sm list-none">
                        <li>• Waktu Perlombaan: <span class="text-white font-bold uppercase tracking-widest">09:00 - 21:00 WIB</span> (12 Jam Non-stop).</li>
                        <li>• <span class="text-cyber-500 font-bold uppercase">2 WAVE SYSTEM:</span> Kompetisi dijalankan dalam 2 gelombang serangan intensitas tinggi.</li>
                        <li>• Format: CTF Jeopardy Style (Web, Crypto, Rev, Forensics, Pwn).</li>
                    </ul>
                </div>

                <div class="glass p-8 rounded-2xl accent-border border-red-500/50">
                    <h3 class="text-xl font-bold mb-4 text-red-400 mono uppercase tracking-wider">04. Ethics & Integrity</h3>
                    <ul class="space-y-3 text-gray-400 text-sm list-none">
                        <li>• <span class="text-white font-bold underline italic uppercase">ZERO TOLERANCE:</span> Bruteforce, DDoS, atau serangan apapun ke infrastruktur lomba akan dilakukan **diskualifikasi saat itu juga!**</li>
                        <li>• <span class="text-white font-bold">VERIFIKASI:</span> Tunggu verifikasi admin maks <span class="text-white">2x24 jam</span>. Jika belum dapat email balasan, hubungi <span class="text-white font-extrabold uppercase underline">@milly10</span> via Discord!</li>
                        <li>• Dilarang keras melakukan sharing flag atau kolaborasi antar tim berbeda.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="timeline" class="py-20 px-6">
        <div class="container mx-auto max-w-4xl">
            <h2 class="text-3xl font-bold text-center mb-16 text-cyber-500 tracking-widest uppercase">Event Timeline</h2>
            
            <div class="space-y-12">
                <div class="relative pl-12 md:pl-0">
                    <div class="md:flex items-center">
                        <div class="hidden md:flex w-1/2 justify-end pr-10 text-right">
                            <span class="text-cyber-500 font-bold mono">18 MEI 2026</span>
                        </div>
                        <div class="absolute left-0 md:static w-8 h-8 bg-cyber-500 rounded-full border-4 border-gray-900 z-10 cyber-glow"></div>
                        <div class="md:w-1/2 md:pl-10">
                            <h4 class="font-bold text-xl uppercase">Open Registration</h4>
                            <p class="text-gray-400 text-sm">Deployment portal pendaftaran resmi dibuka.</p>
                            <span class="md:hidden text-cyber-500 font-bold mono text-xs mt-1 block">18 MEI 2026</span>
                        </div>
                    </div>
                </div>

                <div class="relative pl-12 md:pl-0">
                    <div class="md:flex items-center">
                        <div class="md:w-1/2 md:pr-10 md:text-right">
                            <h4 class="font-bold text-xl uppercase tracking-widest">Close Registration</h4>
                            <p class="text-gray-400 text-sm">Batas akhir pendaftaran tim dan verifikasi data peserta.</p>
                            <span class="md:hidden text-cyber-500 font-bold mono text-xs mt-1 block">20 JUNI 2026</span>
                        </div>
                        <div class="absolute left-0 md:static w-8 h-8 bg-gray-700 rounded-full border-4 border-gray-900 z-10"></div>
                        <div class="hidden md:flex w-1/2 pl-10">
                            <span class="text-cyber-500 font-bold mono">20 JUNI 2026</span>
                        </div>
                    </div>
                </div>

                <div class="relative pl-12 md:pl-0">
                    <div class="md:flex items-center">
                        <div class="hidden md:flex w-1/2 justify-end pr-10 text-right">
                            <span class="text-cyber-500 font-bold mono">27 JUNI 2026</span>
                        </div>
                        <div class="absolute left-0 md:static w-8 h-8 bg-cyber-500 rounded-full border-4 border-gray-900 z-10 cyber-glow"></div>
                        <div class="md:w-1/2 md:pl-10">
                            <h4 class="font-bold text-xl text-cyber-500 uppercase tracking-widest">Competition Day</h4>
                            <p class="text-gray-400 text-sm font-bold uppercase">09:00 - 21:00 (2 Waves Battle).</p>
                            <span class="md:hidden text-cyber-500 font-bold mono text-xs mt-1 block">27 JUNI 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-12 border-t border-gray-800 glass mt-20 text-center">
        <div class="container mx-auto px-6 text-[10px] tracking-[0.4em] uppercase opacity-60">
            &copy; <?php echo date("Y"); ?> Organized by Darmajaya Cyber Security Club
        </div>
    </footer>

</body>
</html>