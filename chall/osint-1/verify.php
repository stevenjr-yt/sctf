<?php
// Pastikan response selalu dalam format JSON
header('Content-Type: application/json');

// Mengambil payload JSON dari frontend
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['lat']) || !isset($data['lng'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Koordinat tidak ditemukan.'
    ]);
    exit;
}

$user_lat = (float) $data['lat'];
$user_lng = (float) $data['lng'];

// ==========================================
// KONFIGURASI CHALLENGE
// ==========================================
// Target lokasi: Bandara Muhammad Taufiq Kiemas Krui
$target_lat = -5.211513;
$target_lng = 103.941422;

// Flag yang akan diberikan
$flag = "SCTF26{w3lc0m3_t0_l4mpung_0s1nt_m4st3r}";

// Toleransi jarak maksimum dalam METER (biar ga perlu presisi sampai desimal ke-10)
$tolerance_meters = 50; 
// ==========================================

// Fungsi untuk menghitung jarak dua titik koordinat (Haversine Formula)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // Radius bumi dalam meter

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * asin(sqrt($a));
    $distance = $earth_radius * $c;

    return $distance;
}

$distance = calculateDistance($user_lat, $user_lng, $target_lat, $target_lng);
$rounded_distance = round($distance);

// Validasi apakah jarak tebakan berada di dalam radius toleransi
if ($distance <= $tolerance_meters) {
    echo json_encode([
        'success' => true,
        'flag' => $flag,
        'distance' => $rounded_distance
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Titik belum pas! Coba perhatikan lagi clue fotonya.',
        'distance' => $rounded_distance
    ]);
}
?>