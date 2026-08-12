<?php
$host = "192.168.1.10";
$user = "root";
$pass = "root";
$db   = "sctf"; // Ganti sama nama database lu nanti

$conn = mysqli_connect($host, $user, $pass, $db);

// =========================================================================
// WEB APPLICATION FIREWALL (WAF) - IP & DEVICE BLOCKER + WHITELIST
// =========================================================================
if (isset($conn) && $conn instanceof mysqli) {
    
    // 1. Auto-create tabel untuk Log Serangan dan Daftar IP Diblokir
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `sparta_security_logs` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip_address` varchar(50) NOT NULL,
      `threat_type` varchar(50) NOT NULL,
      `payload` text NOT NULL,
      `waktu` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    )");

    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `sparta_blocked_ips` (
      `ip_address` varchar(50) NOT NULL,
      `waktu_blokir` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`ip_address`)
    )");

    // 2. Ambil IP Pengunjung
    $ip_visitor = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $safe_ip = mysqli_real_escape_string($conn, $ip_visitor);

    // =====================================================================
    // 👑 WHITELIST DEVELOPER (ANTI SENJATA MAKAN TUAN)
    // =====================================================================
    $whitelist_ips = [
        '127.0.0.1', // Localhost IPv4
        '::1',       // Localhost IPv6
        '2001:448a:114a:112c:186a:930d:8220:91f2' // IP Lu Bro!
    ];

    // JIKA IP ADA DI WHITELIST, LEWATI SEMUA PENGECEKAN KEAMANAN
    if (!in_array($ip_visitor, $whitelist_ips)) {

        // 3. CEK APAKAH PERANGKAT (COOKIE) ATAU IP SUDAH DIBLOKIR?
        $is_banned = false;

        // Cek Cookie (Device Ban)
        if (isset($_COOKIE['sparta_device_banned']) && $_COOKIE['sparta_device_banned'] === 'true') {
            $is_banned = true;
        } else {
            // Cek IP Database
            $cek_blokir = @mysqli_query($conn, "SELECT ip_address FROM sparta_blocked_ips WHERE ip_address = '$safe_ip'");
            if ($cek_blokir && mysqli_num_rows($cek_blokir) > 0) {
                $is_banned = true;
                // Tanam cookie ban lagi
                setcookie('sparta_device_banned', 'true', time() + (10 * 365 * 24 * 60 * 60), '/'); 
            }
        }

        // Eksekusi Blokir jika terdeteksi (Lempar ke ban.php)
        if ($is_banned) {
            header("Location: ban.php");
            exit;
        }

        // 4. Tangkap payload untuk pengecekan ancaman (Jika belum diblokir)
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $payload_str = urldecode($request_uri . ' | POST: ' . json_encode($_POST));
        
        $is_threat = false;
        $threat_type = "";
        
        // Deteksi Pola SQL Injection
        $sqli_patterns = ['/UNION\s+SELECT/i', '/--/'];
        foreach ($sqli_patterns as $pattern) {
            if (preg_match($pattern, $payload_str)) {
                $is_threat = true;
                $threat_type = "SQL Injection Attempt";
                break;
            }
        }
        
        // Deteksi Pola XSS (Cross Site Scripting)
        if (!$is_threat) {
            $xss_patterns = ['/<script>/i', '/javascript:/i', '/onerror=/i', '/onload=/i'];
            foreach ($xss_patterns as $pattern) {
                if (preg_match($pattern, $payload_str)) {
                    $is_threat = true;
                    $threat_type = "XSS Attack Attempt";
                    break;
                }
            }
        }
        
        // 5. JIKA TERDETEKSI ANCAMAN: CATAT LOG, BLOKIR IP, & TANEM COOKIE BAN!
        if ($is_threat) {
            $safe_payload = mysqli_real_escape_string($conn, substr($payload_str, 0, 500));
            $safe_threat = mysqli_real_escape_string($conn, $threat_type);
            
            // Masukkan data ke log
            @mysqli_query($conn, "INSERT INTO sparta_security_logs (ip_address, threat_type, payload) VALUES ('$safe_ip', '$safe_threat', '$safe_payload')");
            
            // Masukkan IP ke database daftar hitam
            @mysqli_query($conn, "INSERT IGNORE INTO sparta_blocked_ips (ip_address) VALUES ('$safe_ip')");
            
            // Tanam Cookie Haram
            setcookie('sparta_device_banned', 'true', time() + (10 * 365 * 24 * 60 * 60), '/'); 
            
            // Langsung lempar ke halaman Banned
            header("Location: ban.php");
            exit;
        }
    }
}
?>