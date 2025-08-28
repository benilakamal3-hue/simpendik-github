<?php
include '../db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Proses Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $judul = $conn->real_escape_string($_POST['judul']);
    $kategori = $conn->real_escape_string($_POST['kategori']);
    $file = $_FILES['file'];
    
    $target_dir = "uploads/arsip/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nama_file = "arsip_" . time() . "." . $file_ext;
    $target_file = $target_dir . $nama_file;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO arsip_digital (judul, kategori, nama_file) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $judul, $kategori, $nama_file);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'File berhasil diupload!'
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Gagal mengupload file!'
        ];
    }
    
    header("Location: arsip.php");
    exit;
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("SELECT nama_file FROM arsip_digital WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $file_path = "uploads/arsip/" . $data['nama_file'];
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $stmt = $conn->prepare("DELETE FROM arsip_digital WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'File berhasil dihapus!'
        ];
    }
    
    header("Location: arsip.php");
    exit;
}

// Pencarian
$search = isset($_GET['cari']) ? $conn->real_escape_string($_GET['cari']) : '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE judul LIKE ? OR kategori LIKE ?";
    $search_term = "%$search%";
    $params = [$search_term, $search_term];
}

$sql = "SELECT * FROM arsip_digital $where ORDER BY tanggal_upload DESC";
$stmt = $conn->prepare($sql);

if (!empty($where)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Digital - Monev Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
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
            margin-bottom: 25px;
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

        .card-header {
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
        }

        /* Form elements */
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: var(--text-primary);
            border-radius: var(--border-radius);
            padding: 12px 15px;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(76, 201, 240, 0.25);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 8px;
        }

        /* Buttons */
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

        .btn-outline-primary {
            border: 1px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid var(--text-muted);
            color: var(--text-muted);
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: var(--text-muted);
            color: var(--text-primary);
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
            font-weight: 600;
            color: var(--accent);
        }

        .table td {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        /* Badge Styles */
        .badge {
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 20px;
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
            .sidebar-toggle {
                left: 20px;
            }
        }

        /* Highlight Search */
        .highlight {
            background-color: var(--warning);
            color: black;
            padding: 0 2px;
            border-radius: 3px;
        }

        /* File view button */
        .btn-view {
            background: rgba(76, 201, 240, 0.2);
            color: var(--accent);
            border: none;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .btn-view:hover {
            background: rgba(76, 201, 240, 0.4);
            transform: translateY(-2px);
        }

        /* Input group */
        .input-group .form-control {
            border-right: none;
        }

        .input-group .btn {
            border-left: none;
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
                    <i class="bi bi-folder text-white"></i>
                </div>
                <span class="d-none d-sm-inline">Monitoring & Evaluasi</span>
            </a>
            <div class="d-flex align-items-center">
                <div class="user-greeting me-3">
                    <span id="greeting-text"></span>
                    <strong><?= htmlspecialchars($_SESSION['user']['nama_lengkap'] ?? 'Pegawai') ?></strong>
                    <small class="d-block text-muted"><?= htmlspecialchars($_SESSION['user']['jabatan'] ?? 'Staff') ?></small>
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
        </a>
        <a href="arsip.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Arsip Digital">
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
        <a href="riwayat_hidup.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Riwayat Hidup">
            <i class="bi bi-file-text"></i>
            <span>Riwayat Hidup</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-folder me-2"></i> Arsip Digital</h2>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2" id="time-badge"></span>
                    <span class="badge bg-secondary"><?php echo date('d F Y'); ?></span>
                </div>
            </div>

            <!-- Form Upload -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cloud-arrow-up me-2"></i> Upload Dokumen</h5>
                    <p class="text-muted mb-0">Unggah dokumen untuk disimpan dalam arsip digital</p>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Judul Dokumen</label>
                                <input type="text" name="judul" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Laporan">Laporan</option>
                                    <option value="SK">Surat Keputusan</option>
                                    <option value="Surat Tugas">Surat Tugas</option>
                                    <option value="Dokumen Lain">Dokumen Lain</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">File Dokumen</label>
                                <input type="file" name="file" class="form-control" required>
                                <small class="text-muted">Format: PDF, DOCX, JPG, PNG (Maks. 5MB)</small>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" name="upload" class="btn btn-success">
                                    <i class="bi bi-upload me-2"></i>Upload Dokumen
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Pencarian -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-search me-2"></i> Cari Arsip</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="input-group">
                            <input type="text" name="cari" class="form-control" placeholder="Cari berdasarkan judul atau kategori..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search me-2"></i>Cari
                            </button>
                            <?php if (!empty($search)): ?>
                            <a href="arsip.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Daftar Arsip -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-archive me-2"></i> Daftar Arsip</h5>
                    <p class="text-muted mb-0">Dokumen yang telah tersimpan dalam sistem</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Judul</th>
                                    <th width="15%">Kategori</th>
                                    <th width="20%">File</th>
                                    <th width="20%">Tanggal Upload</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <?php if (!empty($search)): ?>
                                                    <?= preg_replace("/(" . preg_quote($search, '/') . ")/i", "<span class='highlight'>$1</span>", htmlspecialchars($row['judul'])) ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($row['judul']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($search)): ?>
                                                    <?= preg_replace("/(" . preg_quote($search, '/') . ")/i", "<span class='highlight'>$1</span>", htmlspecialchars($row['kategori'])) ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($row['kategori']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="uploads/arsip/<?= htmlspecialchars($row['nama_file']) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-view">
                                                    <i class="bi bi-eye me-1"></i>Lihat Dokumen
                                                </a>
                                            </td>
                                            <td><?= date('d M Y H:i', strtotime($row['tanggal_upload'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-danger btn-hapus" 
                                                        data-id="<?= $row['id'] ?>"
                                                        data-bs-toggle="tooltip" 
                                                        title="Hapus Dokumen">
                                                    <i class="bi bi-trash me-1"></i>Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">Tidak ada dokumen ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Dynamic greeting
        const hour = new Date().getHours();
        let greeting = '';
        if (hour >= 5 && hour < 11) greeting = 'Selamat Pagi, ';
        else if (hour >= 11 && hour < 15) greeting = 'Selamat Siang, ';
        else if (hour >= 15 && hour < 19) greeting = 'Selamat Sore, ';
        else greeting = 'Selamat Malam, ';
        $('#greeting-text').text(greeting);

        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
            });
        }

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Confirm delete
        $('.btn-hapus').click(function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Apakah Anda yakin ingin menghapus dokumen ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'arsip.php?hapus=' + id;
                }
            });
        });

        // Show alerts
        <?php if (isset($_SESSION['alert'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['alert']['type'] ?>',
                title: '<?= $_SESSION['alert']['type'] === 'success' ? 'Berhasil!' : 'Gagal!' ?>',
                text: '<?= $_SESSION['alert']['message'] ?>',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>