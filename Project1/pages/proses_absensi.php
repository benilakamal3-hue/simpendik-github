<?php
// proses_absensi.php
session_start();
require_once __DIR__ . '/../db_config.php';

// Pastikan output hanya JSON
header('Content-Type: application/json');

// Konfigurasi koordinat kantor
define('KANTOR_LAT', -10.155506); // Ganti dengan koordinat kantor Anda
define('KANTOR_LNG', 123.619988);

// Cek session user
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'message' => 'Anda belum login']));
}

$pegawai_id = intval($_SESSION['user']['id'] ?? 0);
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

try {
    if ($action === 'status') {
        handleStatusAction($conn, $pegawai_id);
    } elseif ($action === 'do') {
        handleDoAction($conn, $pegawai_id);
    } else {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']));
    }
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()]));
}

function handleStatusAction($conn, $pegawai_id) {
    // Ambil data JSON dari body request
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'Data request tidak valid']));
    }

    $lat = floatval($input['lat'] ?? 0);
    $lng = floatval($input['lng'] ?? 0);
    
    // Cek status presensi hari ini
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT 
        COUNT(CASE WHEN jenis = 'masuk' THEN 1 END) as has_masuk,
        COUNT(CASE WHEN jenis = 'keluar' THEN 1 END) as has_keluar
        FROM presensi 
        WHERE pegawai_id = ? AND tanggal = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare statement gagal: ' . $conn->error);
    }
    
    $stmt->bind_param('is', $pegawai_id, $today);
    if (!$stmt->execute()) {
        throw new Exception('Execute statement gagal: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Get result gagal: ' . $stmt->error);
    }
    
    $data = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'status' => 'ok',
        'data' => [
            'has_masuk' => $data['has_masuk'] > 0,
            'has_keluar' => $data['has_keluar'] > 0
        ],
        'message' => 'Status presensi hari ini'
    ]);
}

function handleDoAction($conn, $pegawai_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'Data request tidak valid']));
    }

    $lat = floatval($input['lat'] ?? 0);
    $lng = floatval($input['lng'] ?? 0);
    $actionType = isset($input['action']) ? trim($input['action']) : '';
    
    // Validasi jenis aksi
    if (!in_array($actionType, ['masuk', 'keluar'])) {
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']));
    }
    
    // Dapatkan alamat dari koordinat
    $lokasi = getLocationFromCoordinates($lat, $lng);
    
    // Hitung jarak dari kantor
    $jarak = haversineDistance($lat, $lng, KANTOR_LAT, KANTOR_LNG);
    
    // Simpan ke database
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    // Cek duplikasi presensi
    $stmt = $conn->prepare("SELECT id FROM presensi WHERE pegawai_id = ? AND tanggal = ? AND jenis = ?");
    if (!$stmt) {
        throw new Exception('Prepare statement gagal: ' . $conn->error);
    }
    
    $stmt->bind_param('iss', $pegawai_id, $today, $actionType);
    if (!$stmt->execute()) {
        throw new Exception('Execute statement gagal: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stmt->close();
        http_response_code(400);
        die(json_encode(['status' => 'error', 'message' => 'Anda sudah melakukan presensi ' . $actionType . ' hari ini']));
    }
    $stmt->close();
    
    // Simpan presensi
    $stmt = $conn->prepare("INSERT INTO presensi 
        (pegawai_id, tanggal, jam_$actionType, lokasi_$actionType, jarak_$actionType, jenis) 
        VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        throw new Exception('Prepare statement gagal: ' . $conn->error);
    }
    
    $stmt->bind_param('isssds', $pegawai_id, $today, $now, $lokasi, $jarak, $actionType);
    if (!$stmt->execute()) {
        throw new Exception('Execute statement gagal: ' . $stmt->error);
    }
    $stmt->close();
    
    echo json_encode([
        'status' => 'ok',
        'message' => 'Presensi ' . $actionType . ' berhasil dicatat',
        'data' => [
            'tanggal' => $today,
            'jam' => $now,
            'lokasi' => $lokasi,
            'jarak' => $jarak,
            'jenis' => $actionType
        ]
    ]);
}

function getLocationFromCoordinates($lat, $lng) {
    $lokasi = "Koordinat: $lat, $lng";
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1";
    
    $options = [
        'http' => [
            'header' => "User-Agent: MyApp/1.0\r\n"
        ]
    ];
    
    $context = stream_context_create($options);
    $alamat = @file_get_contents($url, false, $context);
    
    if ($alamat) {
        $alamatData = json_decode($alamat, true);
        if (isset($alamatData['display_name'])) {
            return $alamatData['display_name'];
        }
    }
    
    return $lokasi;
}

function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371000; // Radius bumi dalam meter
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c;
}