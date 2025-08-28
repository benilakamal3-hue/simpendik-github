<?php
session_start();
include("../db_config.php");

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['nama_lengkap' => 'Pegawai 1', 'jabatan' => 'Staff Administrasi', 'nip' => '196501011990031001'];
}
$user = $_SESSION['user'];

// Fungsi untuk mengambil riwayat laporan kinerja
function getReportHistory($conn, $nip, $limit = 12) {
    $query = "SELECT * FROM laporan_kinerja 
              WHERE nip = ? 
              ORDER BY periode DESC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $nip, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
    
    return $reports;
}

// Fungsi untuk mengambil detail laporan
function getReportDetail($conn, $id, $nip) {
    $query = "SELECT * FROM laporan_kinerja 
              WHERE id = ? AND nip = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $id, $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Proses penghapusan laporan jika ada permintaan
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $query = "DELETE FROM laporan_kinerja 
              WHERE id = ? AND nip = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $id, $user['nip']);
    
    if ($stmt->execute()) {
        $successMessage = "Laporan berhasil dihapus!";
    } else {
        $errorMessage = "Gagal menghapus laporan.";
    }
}

// Ambil riwayat laporan
$reportHistory = getReportHistory($conn, $user['nip']);

// Ambil detail laporan jika ada permintaan
$reportDetail = null;
if (isset($_GET['view']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $reportDetail = getReportDetail($conn, $id, $user['nip']);
}

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Laporan Kinerja - Monev Pegawai</title>
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

        /* History Card Styles */
        .history-card {
            background: rgba(31, 41, 55, 0.6);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 30px;
            margin-bottom: 30px;
        }

        .history-header {
            border-bottom: 2px solid var(--accent);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .table-dark {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            overflow: hidden;
        }

        .table-dark th {
            background: rgba(67, 97, 238, 0.3);
            border: none;
            padding: 15px;
            font-weight: 600;
        }

        .table-dark td {
            border-color: rgba(255, 255, 255, 0.1);
            padding: 15px;
            vertical-align: middle;
        }

        .table-dark tr:hover {
            background: rgba(67, 97, 238, 0.1);
        }

        .progress {
            height: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--primary), var(--accent));
            transition: width 1.5s ease;
        }

        .badge-score {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .btn-action {
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        /* Modal Styles */
        .modal-content {
            background: rgba(31, 41, 55, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--accent);
            color: white;
        }

        .modal-header {
            border-bottom: 1px solid var(--accent);
        }

        .modal-footer {
            border-top: 1px solid var(--accent);
        }

        .detail-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .detail-section h6 {
            color: var(--accent);
            border-bottom: 1px solid rgba(76, 201, 240, 0.3);
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        /* Filter Styles */
        .filter-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(76, 201, 240, 0.25);
            color: white;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
            }
        }

        /* Real-time update indicator */
        .real-time-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .last-updated {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
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
    <a href="laporan.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Laporan Kinerja">
        <i class="bi bi-journal-text"></i>
        <span>Laporan Kinerja</span>
    </a>
    <a href="riwayat.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Riwayat Laporan">
        <i class="bi bi-clock-history"></i>
        <span>Riwayat Laporan</span>
    </a>
    <a href="skp.php" data-bs-toggle="tooltip" data-bs-placement="right" title="SKP Pegawai">
        <i class="bi bi-briefcase"></i>
        <span>SKP Pegawai</span>
    </a>
    <a href="notifikasi.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Notifikasi">
        <i class="bi bi-bell"></i>
        <span>Notifikasi</span>
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
</div>

<!-- Main Content -->
<div class="content">
    <div class="history-card">
        <div class="d-flex justify-content-between align-items-center history-header">
            <div>
                <h2><i class="bi bi-clock-history me-2"></i> Riwayat Laporan Kinerja</h2>
                <p class="text-muted mb-0">Monitor dan kelola laporan kinerja bulanan Anda</p>
            </div>
            <div class="text-end">
                <span class="badge bg-success real-time-badge me-2">
                    <i class="bi bi-circle-fill"></i> Real-time
                </span>
                <div class="last-updated" id="lastUpdated">
                    Terupdate: <?= date('H:i:s') ?>
                </div>
            </div>
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

        <!-- Filter Section -->
        <div class="filter-card mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select class="form-select" id="yearFilter">
                        <option value="">Semua Tahun</option>
                        <?php
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <select class="form-select" id="monthFilter">
                        <option value="">Semua Bulan</option>
                        <option value="01">Januari</option>
                        <option value="02">Februari</option>
                        <option value="03">Maret</option>
                        <option value="04">April</option>
                        <option value="05">Mei</option>
                        <option value="06">Juni</option>
                        <option value="07">Juli</option>
                        <option value="08">Agustus</option>
                        <option value="09">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pencapaian</label>
                    <select class="form-select" id="achievementFilter">
                        <option value="">Semua</option>
                        <option value="90">> 90% (Excellent)</option>
                        <option value="75">> 75% (Good)</option>
                        <option value="60">> 60% (Fair)</option>
                        <option value="0">< 60% (Poor)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="table-responsive">
            <table class="table table-dark table-hover" id="reportsTable">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Pencapaian Target</th>
                        <th>Status</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reportHistory) > 0): ?>
                        <?php foreach ($reportHistory as $report): 
                            $periode = date('F Y', strtotime($report['periode']));
                            $pencapaian = $report['pencapaian_target'];
                            $statusClass = $pencapaian >= 90 ? 'success' : ($pencapaian >= 75 ? 'info' : ($pencapaian >= 60 ? 'warning' : 'danger'));
                            $statusText = $pencapaian >= 90 ? 'Excellent' : ($pencapaian >= 75 ? 'Good' : ($pencapaian >= 60 ? 'Fair' : 'Poor'));
                        ?>
                        <tr data-year="<?= date('Y', strtotime($report['periode'])) ?>" 
                            data-month="<?= date('m', strtotime($report['periode'])) ?>" 
                            data-achievement="<?= $pencapaian ?>">
                            <td>
                                <strong><?= $periode ?></strong>
                                <?php if (date('Y-m', strtotime($report['periode'])) === date('Y-m')): ?>
                                    <span class="badge bg-primary ms-1">Bulan Ini</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                        <div class="progress-bar bg-<?= $statusClass ?>" 
                                             role="progressbar" 
                                             style="width: <?= $pencapaian ?>%;">
                                        </div>
                                    </div>
                                    <span class="badge bg-<?= $statusClass ?> badge-score">
                                        <?= $pencapaian ?>%
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($report['tanggal_update'])) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-info btn-action" 
                                            data-bs-toggle="modal" data-bs-target="#detailModal"
                                            data-id="<?= $report['id'] ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="laporan.php?periode=<?= date('Y-m', strtotime($report['periode'])) ?>" 
                                       class="btn btn-outline-warning btn-action">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-action" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-id="<?= $report['id'] ?>" data-period="<?= $periode ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="bi bi-inbox display-4 d-block text-muted mb-2"></i>
                                <span class="text-muted">Belum ada laporan kinerja.</span>
                                <div class="mt-3">
                                    <a href="laporan.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Buat Laporan Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (count($reportHistory) > 0): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Statistics Card -->
    <div class="history-card mt-4">
        <h5><i class="bi bi-graph-up me-2"></i> Statistik Laporan</h5>
        <div class="row">
            <div class="col-md-3 col-6 mb-3">
                <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                    <h3 class="mb-0"><?= count($reportHistory) ?></h3>
                    <small>Total Laporan</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                    <h3 class="mb-0">
                        <?php
                        $excellentCount = array_reduce($reportHistory, function($carry, $report) {
                            return $carry + ($report['pencapaian_target'] >= 90 ? 1 : 0);
                        }, 0);
                        echo $excellentCount;
                        ?>
                    </h3>
                    <small>Excellent</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                    <h3 class="mb-0">
                        <?php
                        $avgAchievement = count($reportHistory) > 0 ? 
                            array_sum(array_column($reportHistory, 'pencapaian_target')) / count($reportHistory) : 0;
                        echo round($avgAchievement, 1) . '%';
                        ?>
                    </h3>
                    <small>Rata-rata</small>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                    <h3 class="mb-0">
                        <?php
                        $lastReport = count($reportHistory) > 0 ? $reportHistory[0]['pencapaian_target'] : 0;
                        echo $lastReport . '%';
                        ?>
                    </h3>
                    <small>Terakhir</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Laporan Kinerja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <?php if ($reportDetail): ?>
                    <div class="detail-section">
                        <h6><i class="bi bi-calendar me-1"></i> Periode</h6>
                        <p><?= date('F Y', strtotime($reportDetail['periode'])) ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="bi bi-bullseye me-1"></i> Pencapaian Target</h6>
                        <div class="progress mb-2" style="height: 15px;">
                            <div class="progress-bar" 
                                 style="width: <?= $reportDetail['pencapaian_target'] ?>%;">
                            </div>
                        </div>
                        <p class="mb-0"><?= $reportDetail['pencapaian_target'] ?>%</p>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="bi bi-list-task me-1"></i> Aktivitas Utama</h6>
                        <p><?= nl2br(htmlspecialchars($reportDetail['aktivitas_utama'])) ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="bi bi-exclamation-triangle me-1"></i> Kendala</h6>
                        <p><?= nl2br(htmlspecialchars($reportDetail['kendala'] ?? 'Tidak ada kendala')) ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="bi bi-calendar-check me-1"></i> Rencana Bulan Depan</h6>
                        <p><?= nl2br(htmlspecialchars($reportDetail['rencana_bulan_depan'])) ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="bi bi-lightbulb me-1"></i> Kebutuhan Pengembangan</h6>
                        <p><?= nl2br(htmlspecialchars($reportDetail['kebutuhan_pengembangan'] ?? 'Tidak ada')) ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="bi bi-chat-left-text me-1"></i> Catatan Khusus</h6>
                        <p><?= nl2br(htmlspecialchars($reportDetail['catatan_khusus'] ?? 'Tidak ada')) ?></p>
                    </div>
                    
                    <div class="detail-section">
                        <h6><i class="bi bi-clock me-1"></i> Informasi</h6>
                        <p class="mb-0">Terakhir update: <?= date('d/m/Y H:i', strtotime($reportDetail['tanggal_update'])) ?></p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Memuat detail laporan...</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <a href="#" id="editReportBtn" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus laporan untuk periode <strong id="deletePeriod"></strong>?</p>
                <p class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i> Hapus
                </a>
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

    // Filter functionality
    function filterTable() {
        const yearFilter = document.getElementById('yearFilter').value;
        const monthFilter = document.getElementById('monthFilter').value;
        const achievementFilter = document.getElementById('achievementFilter').value;
        
        const rows = document.querySelectorAll('#reportsTable tbody tr');
        
        rows.forEach(row => {
            const year = row.getAttribute('data-year');
            const month = row.getAttribute('data-month');
            const achievement = parseFloat(row.getAttribute('data-achievement'));
            
            let showRow = true;
            
            if (yearFilter && year !== yearFilter) {
                showRow = false;
            }
            
            if (monthFilter && month !== monthFilter) {
                showRow = false;
            }
            
            if (achievementFilter) {
                const filterValue = parseFloat(achievementFilter);
                if (filterValue > 0) {
                    if (achievement < filterValue) {
                        showRow = false;
                    }
                } else {
                    if (achievement >= 60) {
                        showRow = false;
                    }
                }
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }
    
    document.getElementById('yearFilter').addEventListener('change', filterTable);
    document.getElementById('monthFilter').addEventListener('change', filterTable);
    document.getElementById('achievementFilter').addEventListener('change', filterTable);

    // Modal handlers
    const detailModal = document.getElementById('detailModal');
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const reportId = button.getAttribute('data-id');
            const modalBody = detailModal.querySelector('#modalBody');
            const editBtn = detailModal.querySelector('#editReportBtn');
            
            // Show loading state
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Memuat detail laporan...</p>
                </div>
            `;
            
            // Fetch report details
            fetch(`get_report_detail.php?id=${reportId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const report = data.report;
                        modalBody.innerHTML = `
                            <div class="detail-section">
                                <h6><i class="bi bi-calendar me-1"></i> Periode</h6>
                                <p>${new Date(report.periode).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h6><i class="bi bi-bullseye me-1"></i> Pencapaian Target</h6>
                                <div class="progress mb-2" style="height: 15px;">
                                    <div class="progress-bar" 
                                         style="width: ${report.pencapaian_target}%;">
                                    </div>
                                </div>
                                <p class="mb-0">${report.pencapaian_target}%</p>
                            </div>
                            
                            <div class="detail-section">
                                <h6><i class="bi bi-list-task me-1"></i> Aktivitas Utama</h6>
                                <p>${report.aktivitas_utama.replace(/\n/g, '<br>')}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h6><i class="bi bi-exclamation-triangle me-1"></i> Kendala</h6>
                                <p>${report.kendala ? report.kendala.replace(/\n/g, '<br>') : 'Tidak ada kendala'}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h6><i class="bi bi-calendar-check me-1"></i> Rencana Bulan Depan</h6>
                                <p>${report.rencana_bulan_depan.replace(/\n/g, '<br>')}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h6><i class="bi bi-lightbulb me-1"></i> Kebutuhan Pengembangan</h6>
                                <p>${report.kebutuhan_pengembangan ? report.kebutuhan_pengembangan.replace(/\n/g, '<br>') : 'Tidak ada'}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h6><i class="bi bi-chat-left-text me-1"></i> Catatan Khusus</h6>
                                <p>${report.catatan_khusus ? report.catatan_khusus.replace(/\n/g, '<br>') : 'Tidak ada'}</p>
                            </div>
                            
                            <div class="detail-section">
                                <h6><i class="bi bi-clock me-1"></i> Informasi</h6>
                                <p class="mb-0">Terakhir update: ${new Date(report.tanggal_update).toLocaleDateString('id-ID')} ${new Date(report.tanggal_update).toLocaleTimeString('id-ID')}</p>
                            </div>
                        `;
                        
                        // Set edit button href
                        const period = new Date(report.periode).toISOString().slice(0, 7);
                        editBtn.href = `laporan.php?periode=${period}`;
                    } else {
                        modalBody.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i> Gagal memuat detail laporan.
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i> Terjadi kesalahan saat memuat data.
                        </div>
                    `;
                });
        });
    }

    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const reportId = button.getAttribute('data-id');
            const period = button.getAttribute('data-period');
            
            document.getElementById('deletePeriod').textContent = period;
            document.getElementById('confirmDeleteBtn').href = `riwayat.php?delete=1&id=${reportId}`;
        });
    }

    // Real-time updates
    function updateLastUpdated() {
        document.getElementById('lastUpdated').textContent = 
            `Terupdate: ${new Date().toLocaleTimeString('id-ID')}`;
    }
    
    // Update time every minute
    setInterval(updateLastUpdated, 60000);
    
    // Check for new reports every 30 seconds
    setInterval(() => {
        fetch('check_new_reports.php')
            .then(response => response.json())
            .then(data => {
                if (data.hasNewReports) {
                    // Show notification
                    if (Notification.permission === 'granted') {
                        new Notification('Laporan Baru', {
                            body: 'Ada laporan baru yang tersedia.',
                            icon: 'logo2.png'
                        });
                    }
                    
                    // Reload the page to show new data
                    location.reload();
                }
            });
    }, 30000);
    
    // Request notification permission
    if ('Notification' in window) {
        Notification.requestPermission();
    }

    // Export functionality
    document.getElementById('exportPdf')?.addEventListener('click', function() {
        // Simple PDF export simulation
        const button = this;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengekspor...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
            
            // Show success message
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-3';
            alert.innerHTML = `
                <i class="bi bi-check-circle-fill me-2"></i> Laporan berhasil diekspor sebagai PDF.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.history-card').appendChild(alert);
        }, 2000);
    });
});
</script>
</body>
</html>