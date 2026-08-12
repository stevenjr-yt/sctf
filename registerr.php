<?php
include 'koneksi.php';
// Autoload dari Composer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$current_date = date('Y-m-d');
// Logic Harga: 18 - 23 Mei 2026 = 30rb, sisanya 50rb
$price = ($current_date >= '2026-04-18' && $current_date <= '2026-05-23') ? 30000 : 50000;

$provinces = [
    "Aceh", "Sumatera Utara", "Sumatera Barat", "Riau", "Kepulauan Riau", "Jambi", "Bengkulu", "Sumatera Selatan", "Kepulauan Bangka Belitung", "Lampung",
    "DKI Jakarta", "Banten", "Jawa Barat", "Jawa Tengah", "DI Yogyakarta", "Jawa Timur", "Bali", "Nusa Tenggara Barat", "Nusa Tenggara Timur",
    "Kalimantan Barat", "Kalimantan Tengah", "Kalimantan Selatan", "Kalimantan Timur", "Kalimantan Utara",
    "Sulawesi Utara", "Sulawesi Tengah", "Sulawesi Selatan", "Sulawesi Tenggara", "Gorontalo", "Sulawesi Barat",
    "Maluku", "Maluku Utara", "Papua", "Papua Barat", "Papua Tengah", "Papua Pegunungan", "Papua Selatan", "Papua Barat Daya"
];

if (isset($_POST['register'])) {
    $team_name   = mysqli_real_escape_string($conn, $_POST['team_name'] ?? '');
    $category    = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $province    = mysqli_real_escape_string($conn, $_POST['province'] ?? '');
    $source      = "Website";

    // Validasi panjang nama tim maksimal 255 karakter
    if (strlen($team_name) > 255) {
        $message = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-4 rounded-lg mb-6'>FATAL ERROR: Nama tim maksimal 255 karakter!</div>";
    } else {
        // Email & Phone perwakilan tim diambil otomatis dari Anggota 1 (Ketua)
        $email_pendaftar = mysqli_real_escape_string($conn, $_POST['member_email'][0] ?? '');
        $phone_pendaftar = mysqli_real_escape_string($conn, $_POST['member_phone'][0] ?? '');

        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { @mkdir($target_dir, 0777, true); }

        // Handling Upload Files (PDF)
        $id_file = $target_dir . time() . "_ID_" . basename($_FILES["identity_card"]["name"]);
        move_uploaded_file($_FILES["identity_card"]["tmp_name"], $id_file);

        $follow_file = $target_dir . time() . "_FOLLOW_" . basename($_FILES["follow_proof"]["name"]);
        move_uploaded_file($_FILES["follow_proof"]["tmp_name"], $follow_file);

        $pay_file = $target_dir . time() . "_PAY_" . basename($_FILES["payment_proof"]["name"]);
        move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $pay_file);

        // Query INSERT Tim
        $sql = "INSERT INTO teams (team_name, category, province, identity_card, follow_proof, email, phone, source, payment_proof, amount) 
                VALUES ('$team_name', '$category', '$province', '$id_file', '$follow_file', '$email_pendaftar', '$phone_pendaftar', '$source', '$pay_file', '$price')";

        if (mysqli_query($conn, $sql)) {
            $team_id = mysqli_insert_id($conn);
            
            // Simpan Data Anggota
            if (isset($_POST['member_name']) && is_array($_POST['member_name'])) {
                $names = $_POST['member_name']; 
                $users = $_POST['member_username']; 
                $igs   = $_POST['member_ig']; 
                $emails = $_POST['member_email']; 
                $phones = $_POST['member_phone'];

                for ($i = 0; $i < count($names); $i++) {
                    if (!empty(trim($names[$i]))) {
                        $m_name = mysqli_real_escape_string($conn, $names[$i]); 
                        $m_user = mysqli_real_escape_string($conn, $users[$i]);
                        $m_email = mysqli_real_escape_string($conn, $emails[$i]); 
                        $m_phone = mysqli_real_escape_string($conn, $phones[$i]); 
                        $m_ig = mysqli_real_escape_string($conn, $igs[$i]);
                        
                        mysqli_query($conn, "INSERT INTO team_members (team_id, full_name, username, email, phone, instagram) 
                                             VALUES ('$team_id', '$m_name', '$m_user', '$m_email', '$m_phone', '$m_ig')");
                    }
                }
            }

            // --- SMTP EMAIL NOTIFICATION ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cysecdarmajaya@gmail.com';
                $mail->Password   = 'kntepdqvuwknaxmc';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('cysecdarmajaya@gmail.com', 'Panitia SCTF 2026');
                $mail->addAddress($email_pendaftar);

                $mail->isHTML(true);
                $mail->Subject = 'Registration Success | SCTF 2026';
                $mail->Body    = "Halo <b>$team_name</b>,<br><br>
                                  Pendaftaran SCTF 2026 berhasil! Silahkan login discord di link berikut untuk informasi lebih lanjut:<br>
                                  <a href='https://discord.gg/ZCat4Q8xax'>discord.gg/ZCat4Q8xax</a><br><br>
                                  Terima kasih!";

                $mail->send();
                $email_msg = " Email notifikasi berhasil dikirim.";
            } catch (Exception $e) {
                $email_msg = " Gagal mengirim email: {$mail->ErrorInfo}";
            }

            $message = "<div class='bg-green-500/20 border border-green-500 text-green-400 p-4 rounded-lg mb-6 tracking-widest'>SUCCESS: Registration Initialized!$email_msg</div>";
        } else {
            $message = "<div class='bg-red-500/20 border border-red-500 text-red-400 p-4 rounded-lg mb-6'>FATAL ERROR: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SCTF 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'JetBrains Mono', monospace; background-color: #050b14; }
        .glass { background: rgba(10, 25, 47, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(0, 255, 204, 0.2); }
        select option { background: #0a192f; color: white; }
    </style>
</head>
<body class="text-white p-6">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="glass p-8 rounded-3xl shadow-2xl border-b-4 border-cyan-500">
            <h2 class="text-3xl font-extrabold text-cyan-400 mb-2 uppercase tracking-tighter">SCTF 2026 REGISTRATION</h2>
            <p class="text-gray-400 mb-8 text-[10px] uppercase tracking-[0.3em]">Deploy your team into the system.</p>
            
            <?php echo $message; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-xs text-cyan-400 mb-2 uppercase tracking-widest font-bold">Nama Tim</label>
                        <input type="text" name="team_name" maxlength="255" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 outline-none focus:border-cyan-500 text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-cyan-400 mb-2 uppercase tracking-widest font-bold">Kategori</label>
                        <select name="category" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 outline-none focus:border-cyan-500 text-white">
                            <option value="">Pilih Kategori</option>
                            <option value="Mahasiswa">Mahasiswa</option>
                            <option value="Pelajar">Pelajar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-cyan-400 mb-2 uppercase tracking-widest font-bold">Asal Provinsi</label>
                        <select name="province" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-3 outline-none focus:border-cyan-500 text-white">
                            <option value="">Pilih Provinsi</option>
                            <?php foreach($provinces as $prov) echo "<option value='$prov'>$prov</option>"; ?>
                        </select>
                    </div>
                </div>

                <div id="members-container" class="space-y-4">
                    <div class="member-item border border-gray-800 p-5 rounded-xl bg-cyan-500/5 relative">
                        <h4 class="text-xs text-cyan-500 mb-4 font-bold uppercase tracking-widest">Anggota 1 (Ketua)</h4>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Username (SCTF ID)</label>
                                <input type="text" name="member_username[]" required placeholder="pake_underscore" class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Nama Lengkap</label>
                                <input type="text" name="member_name[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Instagram</label>
                                <input type="text" name="member_ig[]" required placeholder="@" class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Email</label>
                                <input type="email" name="member_email[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">WhatsApp</label>
                                <input type="text" name="member_phone[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-member" class="w-full py-3 border border-dashed border-gray-700 rounded-xl text-[10px] text-gray-500 hover:text-cyan-400 hover:border-cyan-500 transition-all font-bold uppercase tracking-widest">+ Add Member</button>

                <div class="p-6 bg-cyan-500/10 border border-cyan-500/30 rounded-2xl space-y-5">
                    <h4 class="text-cyan-400 font-bold uppercase text-xs tracking-widest">Verification & Payment</h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] text-gray-400 uppercase font-bold mb-2">1. Kartu Pelajar/Mahasiswa (Jadikan 1 PDF)</label>
                            <input type="file" name="identity_card" accept=".pdf" required class="text-xs text-gray-500">
                        </div>

                        <div>
                            <label class="block text-[10px] text-gray-400 uppercase font-bold mb-2">2. Bukti Follow @darmajayacysecclub & @stevenjr_yt (Jadikan 1 PDF)</label>
                            <input type="file" name="follow_proof" accept=".pdf" required class="text-xs text-gray-500">
                        </div>
                    </div>

                    <div class="border-y border-cyan-500/20 py-6 text-center">
                        <p class="text-[10px] text-gray-500 mb-2 uppercase font-bold tracking-widest">Total Amount</p>
                        <p class="text-2xl text-white font-bold mb-4">Rp <?php echo number_format($price, 0, ',', '.'); ?></p>
                        <img src="assets/qris.png" alt="QRIS" class="w-44 mx-auto bg-white p-3 rounded-xl mb-3 shadow-lg">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Scan QRIS A.N DARMAJAYA CYBER SECURITY CLUB</p>
                    </div>

                    <div>
                        <label class="block text-[10px] text-cyan-400 uppercase font-bold mb-2 tracking-widest">3. Upload Bukti Pembayaran (PDF)</label>
                        <input type="file" name="payment_proof" accept=".pdf" required class="text-xs text-gray-400">
                    </div>
                </div>

                <button type="submit" name="register" class="w-full bg-cyan-500 text-black font-extrabold py-5 rounded-2xl hover:bg-cyan-400 transition-all uppercase tracking-[0.3em] shadow-[0_10px_30px_rgba(0,255,204,0.2)]">
                    Execute Registration
                </button>
            </form>
        </div>
        <p class="text-center text-gray-600 text-[9px] mt-10 uppercase tracking-[0.5em]">Darmajaya Cyber Security Club | SCTF 2026</p>
    </div>

    <script>
        const container = document.getElementById('members-container');
        const addBtn = document.getElementById('add-member');
        let count = 1;

        addBtn.addEventListener('click', () => {
            if (count >= 3) return;
            count++;
            const div = document.createElement('div');
            div.className = 'member-item border border-gray-800 p-5 rounded-xl bg-cyan-500/5 relative mt-4';
            div.innerHTML = `
                <button type="button" class="remove absolute top-3 right-4 text-red-500 text-[10px] font-extrabold uppercase tracking-widest">Remove</button>
                <h4 class="text-xs text-cyan-500 mb-4 font-bold uppercase tracking-widest">Anggota ${count}</h4>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Username</label><input type="text" name="member_username[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm"></div>
                    <div><label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Nama Lengkap</label><input type="text" name="member_name[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm"></div>
                    <div><label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Instagram</label><input type="text" name="member_ig[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm"></div>
                    <div><label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">Email</label><input type="email" name="member_email[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm"></div>
                    <div><label class="block text-[10px] text-gray-500 mb-1 uppercase font-bold">WhatsApp</label><input type="text" name="member_phone[]" required class="w-full bg-black/50 border border-gray-700 rounded-lg p-2 text-white text-sm"></div>
                </div>`;
            container.appendChild(div);
            div.querySelector('.remove').addEventListener('click', () => { div.remove(); count--; });
        });
    </script>
</body>
</html>