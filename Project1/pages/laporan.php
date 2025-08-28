<?php
session_start();
include("../db_config.php");

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['nama_lengkap' => 'Pegawai 1', 'jabatan' => 'Staff Administrasi', 'nip' => '196501011990031001'];
}
$user = $_SESSION['user'];

// Fungsi untuk mengambil data laporan kinerja bulanan
function getMonthlyReportData($conn, $nip, $bulan, $tahun) {
    $periode = $tahun . '-' . sprintf("%02d", $bulan);
    
    $query = "SELECT * FROM laporan_kinerja 
              WHERE nip = ? AND periode = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $nip, $periode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Fungsi untuk mengambil semua data laporan
function getAllReports($conn, $nip) {
    $query = "SELECT * FROM laporan_kinerja 
              WHERE nip = ? 
              ORDER BY periode DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    return $reports;
}

// Fungsi untuk menyimpan data laporan kinerja
function saveMonthlyReport($conn, $data) {
    // Cek apakah data sudah ada
    $checkQuery = "SELECT id FROM laporan_kinerja 
                   WHERE nip = ? AND periode = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $data['nip'], $data['periode']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Update data yang sudah ada
        $query = "UPDATE laporan_kinerja SET 
                  pencapaian_target = ?, 
                  aktivitas_utama = ?,
                  kendala = ?,
                  rencana_bulan_depan = ?,
                  kebutuhan_pengembangan = ?,
                  catatan_khusus = ?,
                  tanggal_update = NOW()
                  WHERE nip = ? AND periode = ?";
    } else {
        // Insert data baru
        $query = "INSERT INTO laporan_kinerja 
                  (nip, periode, pencapaian_target, aktivitas_utama, kendala, 
                   rencana_bulan_depan, kebutuhan_pengembangan, catatan_khusus, tanggal_update)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    }
    
    $stmt = $conn->prepare($query);
    
    if ($checkResult->num_rows > 0) {
        $stmt->bind_param("dsssssss", 
            $data['pencapaian_target'], 
            $data['aktivitas_utama'],
            $data['kendala'],
            $data['rencana_bulan_depan'],
            $data['kebutuhan_pengembangan'],
            $data['catatan_khusus'],
            $data['nip'],
            $data['periode']
        );
    } else {
        $stmt->bind_param("ssdsssss", 
            $data['nip'],
            $data['periode'],
            $data['pencapaian_target'], 
            $data['aktivitas_utama'],
            $data['kendala'],
            $data['rencana_bulan_depan'],
            $data['kebutuhan_pengembangan'],
            $data['catatan_khusus']
        );
    }
    
    return $stmt->execute();
}

// Proses form jika ada data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportData = [
        'nip' => $user['nip'],
        'periode' => $_POST['periode'],
        'pencapaian_target' => $_POST['pencapaian_target'],
        'aktivitas_utama' => $_POST['aktivitas_utama'],
        'kendala' => $_POST['kendala'],
        'rencana_bulan_depan' => $_POST['rencana_bulan_depan'],
        'kebutuhan_pengembangan' => $_POST['kebutuhan_pengembangan'],
        'catatan_khusus' => $_POST['catatan_khusus']
    ];
    
    if (saveMonthlyReport($conn, $reportData)) {
        $successMessage = "Laporan kinerja berhasil disimpan!";
    } else {
        $errorMessage = "Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.";
    }
}

// Ambil data untuk bulan dan tahun saat ini
$currentMonth = date('m');
$currentYear = date('Y');
$currentPeriod = $currentYear . '-' . sprintf("%02d", $currentMonth);
$existingData = getMonthlyReportData($conn, $user['nip'], $currentMonth, $currentYear);

// Ambil semua laporan untuk riwayat
$allReports = getAllReports($conn, $user['nip']);

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Bulanan - Monev Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --dark: #2c2c45ff;
            --darker: #55648eff;
            --light: #f8f9fa;
            --success: #4ade80;
            --warning: #fbbf24;
            --danger: #f87171;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--darker);
            color: var(--light);
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, var(--dark), var(--darker)) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            height: 70px;
            z-index: 1030;
        }

        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .navbar-brand img {
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: rotate(-5deg) scale(1.1);
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
            background: linear-gradient(180deg, var(--dark), rgba(26, 26, 46, 0.9));
            padding-top: 80px;
            z-index: 1020;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar a i {
            margin-right: 12px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .sidebar a:hover {
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.2), transparent);
            color: white;
            transform: translateX(5px);
        }

        .sidebar a:hover i {
            transform: scale(1.2);
            color: var(--accent);
        }

        .sidebar a.active {
            background: linear-gradient(90deg, var(--primary), transparent);
            color: white;
            box-shadow: inset 3px 0 0 var(--accent);
        }

        .sidebar a.active i {
            color: var(--accent);
        }

        /* Main Content */
        .content {
            margin-left: 250px;
            padding: 30px;
        }

        /* Report Form Styles */
        .report-card {
            background: rgba(31, 41, 55, 0.6);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 30px;
            margin-bottom: 30px;
        }

        .report-header {
            border-bottom: 2px solid var(--accent);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .form-control, .form-select, .form-textarea {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus, .form-textarea:focus {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(76, 201, 240, 0.25);
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent);
        }

        .form-section:hover {
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .target-indicator {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .target-slider {
            flex: 1;
            height: 8px;
            -webkit-appearance: none;
            appearance: none;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            outline: none;
        }

        .target-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--accent);
            cursor: pointer;
            box-shadow: 0 0 10px rgba(76, 201, 240, 0.5);
        }

        .target-value {
            width: 60px;
            text-align: center;
            font-weight: 600;
            margin-left: 15px;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="logo2.png" alt="Logo Dinas" style="height: 40px; margin-right: 10px;">
            <span class="d-none d-sm-inline">Monitoring & Evaluasi</span>
        </a>
        <div class="d-flex align-items-center">
            <div class="user-greeting me-3">
                <span id="greeting-text"></span>
                <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong>
                <small class="d-block text-muted"><?= htmlspecialchars($user['jabatan']) ?></small>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <a href="../dashboard.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Beranda">
        <i class="bi bi-house-fill"></i>
        <span>Dashboard</span>
    </a>
    <a href="laporan.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Laporan Kinerja">
        <i class="bi bi-journal-text"></i>
        <span>Laporan Kinerja</span>
    </a>
    <a href="skp.php" data-bs-toggle="tooltip" data-bs-placement="right" title="SKP Pegawai">
        <i class="bi bi-briefcase"></i>
        <span>SKP Pegawai</span>
    </a>
    <a href="arsip.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Arsip Digital">
        <i class="bi bi-folder"></i>
        <span>Arsip Digital</span>
    </a>
    <a href="absensi.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Absensi Pegawai">
        <i class="bi bi-geo-alt-fill"></i>
        <span>Absensi</span>
    </a>
    <a href="profil.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Profil Pegawai">
        <i class="bi bi-person-lines-fill"></i>
        <span>Profil Pegawai</span>
    </a>
    <a href="riwayat_hidup.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Profil Pegawai">
        <i class="bi bi-file-text"></i>
        <span>Riwayat Hidup</span>
    </a>
</div>

<!-- Main Content -->
<div class="content">
    <div class="report-card">
        <div class="report-header">
            <h2><i class="bi bi-journal-text me-2"></i> Laporan Kinerja Bulanan</h2>
            <p class="text-muted">Isi laporan kinerja bulanan untuk penilaian</p>
        </div>

        <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $successMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $errorMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form id="formLaporan" action="laporan.php" method="POST">
            <!-- Employee Information -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Pegawai</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIP</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nip']) ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bidang</label>
                    <select name="bidang" class="form-select" required>
                        <option value="Sekretariat & Kepegawaian" selected>Sekretariat & Kepegawaian</option>
                        <option>Bidang Pembinaan Pendidikan Dasar</option>
                        <option>Bidang Kebudayaan</option>
                        <option>Bidang Pembinaan PAUD & Non-Formal</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Periode Laporan</label>
                    <input type="month" name="periode" class="form-control" value="<?= $currentPeriod ?>" required>
                </div>
            </div>

            <!-- Pencapaian Target -->
            <div class="form-section">
                <h4 class="mb-3"><i class="bi bi-bullseye me-2"></i> Pencapaian Target</h4>
                
                <div class="mb-3">
                    <label class="form-label">Persentase Pencapaian Target</label>
                    <div class="target-indicator">
                        <input type="range" class="target-slider" id="pencapaian_slider" min="0" max="100" step="1" 
                               value="<?= $existingData ? $existingData['pencapaian_target'] : 0 ?>" 
                               oninput="updateTargetValue(this.value)">
                        <span class="target-value" id="target_value_display"><?= $existingData ? $existingData['pencapaian_target'] : 0 ?>%</span>
                    </div>
                    <input type="hidden" name="pencapaian_target" id="pencapaian_target" 
                           value="<?= $existingData ? $existingData['pencapaian_target'] : 0 ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Aktivitas Utama yang Dilakukan</label>
                    <textarea name="aktivitas_utama" class="form-control form-textarea" rows="4" placeholder="Jelaskan aktivitas utama yang telah dilakukan selama bulan ini..." required><?= $existingData ? htmlspecialchars($existingData['aktivitas_utama']) : '' ?></textarea>
                </div>
            </div>

            <!-- Kendala dan Hambatan -->
            <div class="form-section">
                <h4 class="mb-3"><i class="bi bi-exclamation-triangle me-2"></i> Kendala dan Hambatan</h4>
                
                <div class="mb-3">
                    <label class="form-label">Kendala yang Dihadapi</label>
                    <textarea name="kendala" class="form-control form-textarea" rows="4" placeholder="Jelaskan kendala atau hambatan yang dihadapi selama melaksanakan tugas..."><?= $existingData ? htmlspecialchars($existingData['kendala']) : '' ?></textarea>
                </div>
            </div>

            <!-- Rencana Bulan Depan -->
            <div class="form-section">
                <h4 class="mb-3"><i class="bi bi-calendar-check me-2"></i> Rencana Bulan Depan</h4>
                
                <div class="mb-3">
                    <label class="form-label">Rencana Kerja Bulan Depan</label>
                    <textarea name="rencana_bulan_depan" class="form-control form-textarea" rows="4" placeholder="Jelaskan rencana kerja untuk bulan depan..." required><?= $existingData ? htmlspecialchars($existingData['rencana_bulan_depan']) : '' ?></textarea>
                </div>
            </div>

            <!-- Pengembangan Diri -->
            <div class="form-section">
                <h4 class="mb-3"><i class="bi bi-lightbulb me-2"></i> Pengembangan Diri</h4>
                
                <div class="mb-3">
                    <label class="form-label">Kebutuhan Pengembangan Diri</label>
                    <textarea name="kebutuhan_pengembangan" class="form-control form-textarea" rows="3" placeholder="Jelaskan kebutuhan pengembangan diri atau pelatihan yang diperlukan..."><?= $existingData ? htmlspecialchars($existingData['kebutuhan_pengembangan']) : '' ?></textarea>
                </div>
            </div>

            <!-- Catatan Khusus -->
            <div class="form-section">
                <h4 class="mb-3"><i class="bi bi-chat-left-text me-2"></i> Catatan Khusus</h4>
                
                <div class="mb-3">
                    <label class="form-label">Catatan atau Saran Khusus</label>
                    <textarea name="catatan_khusus" class="form-control form-textarea" rows="3" placeholder="Tambahkan catatan atau saran khusus lainnya..."><?= $existingData ? htmlspecialchars($existingData['catatan_khusus']) : '' ?></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-between mt-5">
                <a href="dashboard.php" class="btn btn-outline-light px-4"><i class="bi bi-arrow-left me-2"></i> Kembali</a>
                <button type="submit" class="btn btn-submit px-4" id="submitBtn"><i class="bi bi-send-check me-2"></i> Simpan Laporan</button>
            </div>
        </form>
    </div>

    <!-- History Card -->
    <div class="report-card mt-4">
        <h5><i class="bi bi-clock-history me-2"></i> Riwayat Laporan</h5>
        <div class="table-responsive">
            <table class="table table-dark table-hover">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Pencapaian Target</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allReports as $report): ?>
                    <tr>
                        <td><?= date('F Y', strtotime($report['periode'])) ?></td>
                        <td>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?= $report['pencapaian_target'] ?>%;">
                                </div>
                            </div>
                            <small><?= $report['pencapaian_target'] ?>%</small>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($report['tanggal_update'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info view-detail" 
                                    data-bs-toggle="tooltip" 
                                    title="Lihat Detail"
                                    data-report='<?= json_encode($report) ?>'>
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Laporan -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="detailModalLabel">Detail Laporan Kinerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Periode:</strong> <span id="detail-periode"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Pencapaian Target:</strong> <span id="detail-pencapaian"></span>%
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Aktivitas Utama:</strong>
                    <div id="detail-aktivitas" class="p-3 bg-secondary rounded mt-1"></div>
                </div>
                
                <div class="mb-3">
                    <strong>Kendala:</strong>
                    <div id="detail-kendala" class="p-3 bg-secondary rounded mt-1"></div>
                </div>
                
                <div class="mb-3">
                    <strong>Rencana Bulan Depan:</strong>
                    <div id="detail-rencana" class="p-3 bg-secondary rounded mt-1"></div>
                </div>
                
                <div class="mb-3">
                    <strong>Kebutuhan Pengembangan:</strong>
                    <div id="detail-pengembangan" class="p-3 bg-secondary rounded mt-1"></div>
                </div>
                
                <div class="mb-3">
                    <strong>Catatan Khusus:</strong>
                    <div id="detail-catatan" class="p-3 bg-secondary rounded mt-1"></div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Tanggal Update:</strong> <span id="detail-update"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Dynamic greeting
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

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Update target value display
function updateTargetValue(value) {
    document.getElementById('target_value_display').textContent = value + '%';
    document.getElementById('pencapaian_target').value = value;
}

// Handle view detail button click
document.querySelectorAll('.view-detail').forEach(button => {
    button.addEventListener('click', function() {
        const reportData = JSON.parse(this.getAttribute('data-report'));
        
        // Populate modal with data
        document.getElementById('detail-periode').textContent = formatPeriode(reportData.periode);
        document.getElementById('detail-pencapaian').textContent = reportData.pencapaian_target;
        document.getElementById('detail-aktivitas').textContent = reportData.aktivitas_utama || '-';
        document.getElementById('detail-kendala').textContent = reportData.kendala || '-';
        document.getElementById('detail-rencana').textContent = reportData.rencana_bulan_depan || '-';
        document.getElementById('detail-pengembangan').textContent = reportData.kebutuhan_pengembangan || '-';
        document.getElementById('detail-catatan').textContent = reportData.catatan_khusus || '-';
        document.getElementById('detail-update').textContent = formatDateTime(reportData.tanggal_update);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        modal.show();
    });
});

// Helper function to format date
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Helper function to format periode
function formatPeriode(periode) {
    const [year, month] = periode.split('-');
    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return `${monthNames[parseInt(month) - 1]} ${year}`;
}

// Form submission handler
document.getElementById("formLaporan").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const btn = document.getElementById("submitBtn");
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...`;
    
    // Show success message after a delay (simulate processing)
    setTimeout(() => {
        // Create success modal
        const successModal = document.createElement('div');
        successModal.className = 'modal fade';
        successModal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-dark text-light">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title"><i class="bi bi-check-circle-fill text-success me-2"></i>Laporan Berhasil Disimpan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Laporan kinerja bulanan telah berhasil disimpan ke sistem.</p>
                        <p class="text-muted mb-0">Laporan telah dikirim ke atasan langsung untuk ditinjau.</p>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Tutup</button>
                        <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(successModal);
        
        const modal = new bootstrap.Modal(successModal);
        modal.show();
        
        // Submit the form after modal is shown
        this.submit();
    }, 1000);
});

// Add visual feedback for form sections
document.querySelectorAll('.form-section').forEach(section => {
    section.addEventListener('mouseenter', function() {
        this.style.transform = 'translateX(5px)';
        this.style.borderLeftWidth = '6px';
    });
    
    section.addEventListener('mouseleave', function() {
        this.style.transform = 'translateX(0)';
        this.style.borderLeftWidth = '4px';
    });
});

// Character counter for textareas
document.querySelectorAll('textarea').forEach(textarea => {
    const counter = document.createElement('div');
    counter.className = 'form-text text-muted text-end';
    counter.innerHTML = '<span class="char-count">0</span>/1000 karakter';
    textarea.parentNode.appendChild(counter);
    
    textarea.addEventListener('input', function() {
        const count = this.value.length;
        counter.querySelector('.char-count').textContent = count;
        
        if (count > 1000) {
            counter.classList.add('text-danger');
        } else {
            counter.classList.remove('text-danger');
        }
    });
    
    // Trigger initial count
    textarea.dispatchEvent(new Event('input'));
});
</script>
</body>
</html>. 