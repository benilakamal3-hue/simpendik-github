<?php
// pages/absensi.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}
require_once __DIR__ . '/../db_config.php';

// Konfigurasi: koordinat kantor dan radius (meter)
define('KANTOR_LAT', -10.155506);   // ganti ke koordinat kantor Anda
define('KANTOR_LNG', 123.619988);
define('MAX_RADIUS_M', 50);

// Ambil data pegawai dari session
$pegawai_id = intval($_SESSION['user']['id'] ?? 0);
$nama_lengkap = is_array($_SESSION['user']) ? $_SESSION['user']['nama_lengkap'] : $_SESSION['user'];
$jabatan = is_array($_SESSION['user']) ? $_SESSION['user']['jabatan'] : '';

// Ambil riwayat presensi 50 data terakhir
$stmt = $conn->prepare("SELECT * FROM presensi WHERE pegawai_id = ? ORDER BY tanggal DESC LIMIT 50");
$stmt->bind_param('i', $pegawai_id);
$stmt->execute();
$riwayat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tentukan waktu hari untuk background dinamis
$current_hour = date('G');
$time_of_day = 'pagi'; // default

if ($current_hour >= 5 && $current_hour < 11) {
    $time_of_day = 'pagi';
} elseif ($current_hour >= 11 && $current_hour < 15) {
    $time_of_day = 'siang';
} elseif ($current_hour >= 15 && $current_hour < 19) {
    $time_of_day = 'sore';
} else {
    $time_of_day = 'malam';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi - Monev Pegawai</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --dark: #1e293b;
            --darker: #0f172a;
            --light: #f8f9fa;
            --light-bg: rgba(255, 255, 255, 0.1);
            --card-bg: rgba(101, 118, 145, 0.9);
            --success: #4ade80;
            --warning: #fbbf24;
            --danger: #f87171;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
            overflow-x: hidden;
            min-height: 100vh;
            transition: background 1.5s ease;
        }

        /* Background container */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
        }

        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: opacity 1.5s ease;
        }

        /* Background berdasarkan waktu */
        .bg-pagi {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        .bg-siang {
            background-image: linear-gradient(rgada(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        .bg-sore {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        .bg-malam {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        /* Overlay untuk meningkatkan kontras */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: rgba(30, 41, 59, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            height: 70px;
            z-index: 1030;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--text-primary) !important;
        }

        .user-greeting {
            position: relative;
            padding-right: 20px;
        }

        .user-greeting::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 30px;
            width: 2px;
            background: rgba(255, 255, 255, 0.2);
        }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            padding-top: 80px;
            z-index: 1020;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
            transform: translateX(0);
            transition: var(--transition);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-collapsed .sidebar {
            transform: translateX(-250px);
        }

        .sidebar a {
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 8px 15px;
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .sidebar a i {
            margin-right: 12px;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .sidebar a:hover {
            background: rgba(67, 97, 238, 0.15);
            color: var(--text-primary);
            transform: translateX(5px);
        }

        .sidebar a:hover i {
            transform: scale(1.2);
            color: var(--accent);
        }

        .sidebar a.active {
            background: rgba(67, 97, 238, 0.25);
            color: var(--text-primary);
            box-shadow: inset 3px 0 0 var(--accent);
        }

        .sidebar a.active i {
            color: var(--accent);
        }

        /* Main Content */
        .content {
            margin-left: 250px;
            padding: 30px;
            transition: var(--transition);
            position: relative;
            z-index: 1;
        }

        .sidebar-collapsed .content {
            margin-left: 0;
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            border: none;
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            margin-bottom: 20px;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            z-index: 1;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        /* Map Styles */
        #map {
            height: 320px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Button Styles */
        .btn {
            border-radius: var(--border-radius);
            padding: 12px 20px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.5);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #22c55e);
            box-shadow: 0 4px 15px rgba(74, 222, 128, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 222, 128, 0.5);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #ef4444);
            box-shadow: 0 4px 15px rgba(248, 113, 113, 0.3);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(248, 113, 113, 0.5);
        }

        .btn-outline-light {
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: var(--text-primary);
            background: transparent;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            color: var(--text-primary);
        }

        /* Table Styles */
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .table {
            background: rgba(31, 41, 55, 0.6);
            color: var(--text-primary);
            margin-bottom: 0;
        }

        .table thead {
            background: rgba(67, 97, 238, 0.2);
        }

        .table th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
        }

        .table td {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        /* Status Text */
        .status-text {
            font-weight: 500;
            color: var(--accent);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-250px);
            }
            .sidebar-collapsed .sidebar {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }

        /* Print Styles */
        @media print {
            body * { visibility: hidden; }
            #rekapAbsen, #rekapAbsen * { visibility: visible; }
            #rekapAbsen { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%;
                background: white !important;
                color: black !important;
            }
            .table {
                background: white !important;
                color: black !important;
            }
        }

        /* Toggle button */
        .sidebar-toggle {
            position: fixed;
            left: 260px;
            top: 80px;
            z-index: 1030;
            background: var(--primary);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .sidebar-toggle:hover {
            transform: scale(1.1);
            background: var(--accent);
        }

        .sidebar-collapsed .sidebar-toggle {
            left: 20px;
        }
    </style>
</head>
<body>
    <!-- Background Container -->
    <div class="background-container">
        <div class="background-image bg-<?php echo $time_of_day; ?>"></div>
    </div>
    
    <!-- Overlay untuk kontras -->
    <div class="overlay"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="../dashboard.php">
                <div style="height: 40px; width: 40px; background: var(--accent); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                    <i class="bi bi-geo-alt-fill text-white"></i>
                </div>
                <span class="d-none d-sm-inline">Monitoring & Evaluasi</span>
            </a>
            <div class="d-flex align-items-center">
                <div class="user-greeting me-3">
                    <span id="greeting-text"></span>
                    <strong><?= htmlspecialchars($nama_lengkap) ?></strong>
                    <small class="d-block text-muted"><?= htmlspecialchars($jabatan) ?></small>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list text-white"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="../dashboard.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Beranda">
            <i class="bi bi-house-fill"></i>
            <span>Dashboard</span>
        </a>
        <a href="laporan.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Evaluasi Kinerja">
            <i class="bi bi-clipboard-check"></i>
            <span>Laporan Kinerja</span>
        </a>
        <a href="form.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Form Pengajuan">
            <i class="bi bi-file-earmark-text"></i>
            <span>Pengajuan Form</span>
        </a>
        <a href="skp.php" data-bs-toggle="tooltip" data-bs-placement="right" title="SKP Pegawai">
            <i class="bi bi-briefcase"></i>
            <span>SKP Pegawai</span>
        <a href="arsip.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Arsip Digital">
            <i class="bi bi-folder"></i>
            <span>Arsip Digital</span>
        </a>
        <a href="absensi.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Absensi Pegawai">
            <i class="bi bi-geo-alt-fill"></i>
            <span>Absensi</span>
        </a>
        <a href="profil.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Profil Pegawai">
            <i class="bi bi-person-lines-fill"></i>
            <span>Profil Pegawai</span>
        </a>
        <a href="riwayat_hidup.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Riwayat Hidup">
            <i class="bi bi-file-text"></i>
            <span>Riwayat Hidup</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-geo-alt-fill me-2"></i>Absensi Pegawai</h2>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2" id="time-badge"></span>
                    <span class="badge bg-secondary"><?php echo date('d F Y'); ?></span>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="bi bi-calendar-check me-2"></i>Absensi Hari Ini</h5>
                            <div id="map"></div>
                            <p class="mt-2 text-muted small">Lokasi kantor: <?= KANTOR_LAT ?>, <?= KANTOR_LNG ?> — Radius aktif: <?= MAX_RADIUS_M ?> m</p>

                            <div class="mt-3 d-flex gap-2">
                                <button id="btnAction" class="btn btn-success flex-grow-1">Mendeteksi status...</button>
                                <button id="btnRefresh" class="btn btn-outline-light">Refresh Lokasi</button>
                            </div>

                            <p class="mt-3 status-text" id="infoStatus"></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Petunjuk Absensi</h5>
                            <ul class="mb-0">
                                <li>Aktifkan GPS/Location di perangkat Anda</li>
                                <li>Gunakan tombol <strong>Refresh Lokasi</strong> jika posisi belum muncul</li>
                                <li>Absensi hanya valid jika Anda berada dalam radius <?= MAX_RADIUS_M ?> meter dari kantor</li>
                                <li>Pastikan koneksi internet stabil selama proses absensi</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card" id="rekapAbsen">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Rekap Presensi Terbaru</h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="cetakRekap()">
                                    <i class="bi bi-printer-fill"></i> Cetak
                                </button>
                            </div>
                            <div class="table-responsive" style="max-height:420px;">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Masuk</th>
                                            <th>Keluar</th>
                                            <th>Jarak (m)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($riwayat)): ?>
                                            <tr><td colspan="4" class="text-center text-muted">Belum ada riwayat</td></tr>
                                        <?php else: foreach ($riwayat as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($r['tanggal']) ?></td>
                                                <td>
                                                    <?= $r['jam_masuk'] ?? '-' ?>
                                                    <?php if(isset($r['lokasi_masuk'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($r['lokasi_masuk']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= $r['jam_keluar'] ?? '-' ?>
                                                    <?php if(isset($r['lokasi_keluar'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($r['lokasi_keluar']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        $jm = $r['jarak_masuk'] !== null ? number_format($r['jarak_masuk'],2) . ' / ' : '';
                                                        $jk = $r['jarak_keluar'] !== null ? number_format($r['jarak_keluar'],2) : '';
                                                        echo htmlspecialchars($jm . $jk);
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Config
    const kantorLat = <?= json_encode(KANTOR_LAT) ?>;
    const kantorLng = <?= json_encode(KANTOR_LNG) ?>;
    const maxRadius = <?= json_encode(MAX_RADIUS_M) ?>;
    const pegawaiId = <?= json_encode($pegawai_id) ?>;

    // Inisialisasi map
    const map = L.map('map').setView([kantorLat, kantorLng], 17);

    // Layer peta
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
        maxZoom: 22,
        attribution: '&copy; <a href="https://www.openstreetmap.org/?#map=19/-10.155063/123.619433">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Tambahkan marker kantor
    const kantorIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    L.marker([kantorLat, kantorLng], {icon: kantorIcon}).addTo(map)
        .bindPopup('🏢 Lokasi Kantor').openPopup();

    // Tambahkan radius
    L.circle([kantorLat, kantorLng], {
        radius: maxRadius,
        color: '#4361ee',
        fillColor: '#4cc9f0',
        fillOpacity: 0.1
    }).addTo(map);

    let userMarker = null;
    let currentLat = null, currentLng = null;
    let todayStatus = null;

    // Fungsi menghitung jarak (Haversine formula)
    function haversine(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Radius bumi dalam meter
        const toRad = (v) => v * Math.PI / 180;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat/2)**2 + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon/2)**2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Update lokasi dan status
    function updateLocationAndStatus(showAlert=false) {
        if (!navigator.geolocation) {
            Swal.fire('Error','Browser Anda tidak mendukung Geolocation.','error');
            return;
        }
        
        document.getElementById('infoStatus').textContent = 'Mendeteksi lokasi...';
        
        navigator.geolocation.getCurrentPosition(
            position => {
                currentLat = position.coords.latitude;
                currentLng = position.coords.longitude;

                // Update marker pengguna
                if (userMarker) userMarker.remove();
                
                const userIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34]
                });
                
                userMarker = L.marker([currentLat, currentLng], {icon: userIcon})
                    .addTo(map)
                    .bindPopup('📍 Lokasi Anda')
                    .openPopup();
                
                map.setView([currentLat, currentLng], 17);

                // Hitung jarak
                const jarak = haversine(currentLat, currentLng, kantorLat, kantorLng);
                document.getElementById('infoStatus').textContent = `Jarak ke kantor: ${Math.round(jarak)} meter (Maks: ${maxRadius} m)`;

                // Cek status absensi
                checkAttendanceStatus(jarak, showAlert);
            }, 
            err => {
                console.error('Geolocation error:', err);
                Swal.fire('Error','Gagal mendapatkan lokasi. Pastikan GPS aktif.','error');
                document.getElementById('infoStatus').textContent = 'Gagal mendapatkan lokasi.';
                updateButtonState(null, true);
            }, 
            { 
                enableHighAccuracy: true, 
                timeout: 10000, 
                maximumAge: 0 
            }
        );
    }

    // Fungsi untuk memeriksa status absensi
    function checkAttendanceStatus(jarak, showAlert=false) {
        fetch('proses_absensi.php?action=status', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ 
                lat: currentLat, 
                lng: currentLng,
                pegawai_id: pegawaiId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(resp => {
            if (resp.status !== 'ok') {
                throw new Error(resp.message || 'Invalid response');
            }
            
            todayStatus = resp.data;
            updateButtonState(jarak);
            
            if (showAlert && resp.message) {
                Swal.fire('Info', resp.message, 'info');
            }
        })
        .catch(error => {
            console.error('Error checking attendance status:', error);
            updateButtonState(null, true);
            Swal.fire('Error', 'Gagal memeriksa status absensi: ' + error.message, 'error');
        });
    }

    // Fungsi untuk memperbarui state tombol
    function updateButtonState(jarak, error=false) {
        const btn = document.getElementById('btnAction');
        const statusEl = document.getElementById('infoStatus');
        
        if (error) {
            btn.textContent = 'Error';
            btn.className = 'btn btn-secondary flex-grow-1';
            btn.disabled = true;
            btn.onclick = null;
            statusEl.textContent = 'Terjadi kesalahan saat memeriksa status';
            return;
        }
        
        if (!todayStatus) {
            btn.textContent = 'Memuat...';
            btn.className = 'btn btn-secondary flex-grow-1';
            btn.disabled = true;
            btn.onclick = null;
            return;
        }
        
        if (!todayStatus.has_masuk) {
            btn.textContent = 'Presensi Masuk';
            btn.className = 'btn btn-success flex-grow-1';
            btn.onclick = () => doAttendance('masuk');
        } else if (!todayStatus.has_keluar) {
            btn.textContent = 'Presensi Keluar';
            btn.className = 'btn btn-danger flex-grow-1';
            btn.onclick = () => doAttendance('keluar');
        } else {
            btn.textContent = 'Sudah Presensi (Selesai)';
            btn.className = 'btn btn-secondary flex-grow-1';
            btn.onclick = null;
        }
        
        if (jarak !== null && jarak > maxRadius) {
            btn.disabled = true;
            statusEl.textContent += ' — Anda berada di luar radius absensi.';
        } else {
            btn.disabled = false;
        }
    }

    // Fungsi untuk melakukan presensi
    function doAttendance(action) {
        if (currentLat === null || currentLng === null) {
            Swal.fire('Info','Silakan izinkan lokasi terlebih dahulu lalu klik Refresh.','info');
            return;
        }
        
        Swal.fire({
            title: `Presensi ${action === 'masuk' ? 'Masuk' : 'Keluar'}`,
            text: `Anda yakin ingin melakukan presensi ${action === 'masuk' ? 'masuk' : 'keluar'}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Presensi',
            cancelButtonText: 'Batal',
            backdrop: true,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                const loadingSwal = Swal.fire({
                    title: 'Memproses...',
                    html: 'Sedang melakukan presensi',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                fetch('proses_absensi.php?action=do', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({ 
                        lat: currentLat, 
                        lng: currentLng, 
                        action: action,
                        pegawai_id: pegawaiId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(resp => {
                    loadingSwal.close();
                    if (resp.status === 'ok') {
                        Swal.fire({
                            title: 'Sukses',
                            text: resp.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        throw new Error(resp.message || 'Terjadi kesalahan');
                    }
                })
                .catch(error => {
                    loadingSwal.close();
                    console.error('Attendance error:', error);
                    Swal.fire({
                        title: 'Gagal',
                        text: 'Gagal melakukan presensi: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    }

    // Event Listeners
    document.getElementById('btnRefresh').addEventListener('click', () => {
        updateLocationAndStatus(true);
    });

    // Fungsi cetak rekap
    function cetakRekap() {
        window.print();
    }

    // Dynamic greeting
    document.addEventListener("DOMContentLoaded", function() {
        const greetingEl = document.getElementById("greeting-text");
        if (greetingEl) {
            const now = new Date();
            const hour = now.getHours();
            let greet = "";
            if (hour >= 5 && hour < 11) greet = "Selamat Pagi, ";
            else if (hour >= 11 && hour < 15) greet = "Selamat Siang, ";
            else if (hour >= 15 && hour < 19) greet = "Selamat Sore, ";
            else greet = "Selamat Malam, ";
            greetingEl.textContent = greet;
        }
        
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
            });
        }
        
        // Inisialisasi lokasi
        updateLocationAndStatus();
        
        // Enable Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
</body>
</html>