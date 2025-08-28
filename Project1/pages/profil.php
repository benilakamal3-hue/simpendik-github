<?php
session_start();
require_once '../db_config.php';

// Cek login
if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil id user yang login
$user_id = $_SESSION['user']['id'];

// Ambil data pegawai
$query = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$pegawai = $result->fetch_assoc();

// Proses simpan profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nip          = trim($_POST['nip']);
    $jabatan      = trim($_POST['jabatan']);
    $email        = trim($_POST['email']);
    $no_hp        = trim($_POST['no_hp']);

    // Upload foto
    $foto = $pegawai['foto'] ?? null;
    if (!empty($_FILES['foto']['name'])) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $ext  = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $filename = "pegawai_" . $user_id . "_" . time() . "." . $ext;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            $foto = $filename;
        }
    }

    // Cek apakah pegawai sudah ada
    $check = $conn->prepare("SELECT id FROM pegawai WHERE id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        // UPDATE
        $sql = "UPDATE pegawai SET 
                    nama_lengkap=?, nip=?, jabatan=?, email=?, no_hp=?, foto=?, updated_at=NOW()
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $nama_lengkap, $nip, $jabatan, $email, $no_hp, $foto, $user_id);
    } else {
        // INSERT
        $sql = "INSERT INTO pegawai (id, nama_lengkap, nip, jabatan, email, no_hp, foto, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $user_id, $nama_lengkap, $nip, $jabatan, $email, $no_hp, $foto);
    }

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profil berhasil disimpan!";
        header("Location: profil.php");
        exit();
    } else {
        $error = "Gagal menyimpan profil: " . $conn->error;
    }
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pegawai - Monev Pegawai</title>
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

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
            overflow-x: hidden;
            min-height: 100vh;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: background 1.5s ease;
        }

        /* Background berdasarkan waktu */
        body.bg-pagi {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        body.bg-siang {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        body.bg-sore {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        body.bg-malam {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
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
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            padding-top: 80px;
            z-index: 1020;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
            transform: translateX(0);
            transition: var(--transition);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
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

        .sidebar a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: 0.5s;
        }

        .sidebar a:hover::before {
            left: 100%;
        }

        /* Main Content */
        .content {
            margin-left: 250px;
            padding: 30px;
            transition: var(--transition);
        }

        /* Cards */
        .card {
            background: var(--card-bg);
            border: none;
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            position: relative;
            z-index: 1;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
            border-bottom: none;
            padding: 25px;
        }

        .card-title {
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: var(--text-primary);
            border-radius: var(--border-radius);
            padding: 14px 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.3rem rgba(76, 201, 240, 0.3);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: var(--border-radius);
            padding: 14px 30px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.5);
        }

        .profile-photo {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--accent);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transition: var(--transition);
        }

        .profile-photo:hover {
            transform: scale(1.08);
            box-shadow: 0 12px 30px rgba(76, 201, 240, 0.5);
        }

        .table {
            background: rgba(30, 41, 59, 0.8);
            border-radius: var(--border-radius);
            overflow: hidden;
            color: var(--text-primary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .table th {
            background: rgba(67, 97, 238, 0.25);
            color: var(--accent);
            font-weight: 600;
            border: none;
            padding: 16px;
            font-size: 1rem;
        }

        .table td {
            border: none;
            padding: 16px;
            vertical-align: middle;
            font-size: 1rem;
        }

        .table tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.06);
        }

        .table tr:hover {
            background: rgba(67, 97, 238, 0.15);
        }

        /* Alert styles */
        .alert {
            border-radius: var(--border-radius);
            border: none;
            backdrop-filter: blur(10px);
            padding: 16px 20px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(74, 222, 128, 0.25);
            color: #4ade80;
            border-left: 4px solid #4ade80;
        }

        .alert-danger {
            background: rgba(248, 113, 113, 0.25);
            color: #f87171;
            border-left: 4px solid #f87171;
        }

        /* Custom file input */
        .form-control[type="file"] {
            padding: 12px;
        }

        .form-control[type="file"]::file-selector-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            margin-right: 12px;
            transition: var(--transition);
            font-weight: 500;
        }

        .form-control[type="file"]::file-selector-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-250px);
                z-index: 1040;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
            .navbar-brand span {
                display: none;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            opacity: 0;
            animation: fadeIn 0.6s forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Particles background */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }

        /* Section headings */
        .section-heading {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(76, 201, 240, 0.3);
        }

        .section-heading::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        /* Toggle button for mobile */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 80px;
            left: 20px;
            z-index: 1050;
            background: var(--primary);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
        }

        .sidebar-toggle:hover {
            background: var(--accent);
            transform: scale(1.1);
        }

        @media (max-width: 992px) {
            .sidebar-toggle {
                display: flex;
            }
        }

        /* Improved contrast for better readability */
        .text-contrast {
            color: var(--text-primary);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .bg-contrast {
            background: rgba(15, 23, 42, 0.7);
            padding: 5px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body class="bg-<?php echo $time_of_day; ?>">
<!-- Particles Background -->
<div id="particles-js"></div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="../dashboard.php">
            <img src="../logo2.png" alt="Logo Dinas" style="height: 40px; margin-right: 10px;">
            <span class="d-none d-sm-inline">Monitoring & Evaluasi</span>
        </a>
        <div class="d-flex align-items-center">
            <div class="user-greeting me-3">
                <span id="greeting-text" class="bg-contrast"></span>
                <strong class="text-contrast"><?= htmlspecialchars($_SESSION['user']['nama_lengkap'] ?? 'User') ?></strong>
                <small class="d-block text-muted"><?= htmlspecialchars($_SESSION['user']['jabatan'] ?? 'Pegawai') ?></small>
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

<!-- Sidebar Toggle for Mobile -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list text-white"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="../dashboard.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Beranda">
        <i class="bi bi-house-fill"></i>
        <span>Dashboard</span>
    </a>
    <a href="evaluasi.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Evaluasi Kinerja">
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
    <a href="arsip.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Arsip Digital">
        <i class="bi bi-folder"></i>
        <span>Arsip Digital</span>
    </a>
    <a href="absensi.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Absensi Pegawai">
        <i class="bi bi-geo-alt-fill"></i>
        <span>Absensi</span>
    </a>
    <a href="profil.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Profil Pegawai">
        <i class="bi bi-person-lines-fill"></i>
        <span>Profil Pegawai</span>
    </a>
    <a href="riwayat_hidup.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Riwayat Hidup">
        <i class="bi bi-file-text"></i>
        <span>Riwayat Hidup</span>
    </a>
</div>

<!-- Content -->
<div class="content">
    <div class="card fade-in delay-1">
        <div class="card-header text-center">
            <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Profil Pegawai</h4>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <!-- Foto Profil -->
                <?php if (!empty($pegawai['foto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($pegawai['foto']); ?>" alt="Foto Profil" class="profile-photo mb-3">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($pegawai['nama_lengkap'] ?? 'User') ?>&background=4361ee&color=fff&size=160" alt="Default Foto" class="profile-photo mb-3">
                <?php endif; ?>
                
                <h5 class="card-title text-contrast"><?= htmlspecialchars($pegawai['nama_lengkap'] ?? 'User') ?></h5>
                <p class="text-muted"><?= htmlspecialchars($pegawai['jabatan'] ?? 'Pegawai') ?></p>
            </div>

            <!-- Pesan -->
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success mt-2"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <!-- Form -->
            <form method="post" enctype="multipart/form-data" class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control"
                        value="<?= htmlspecialchars($pegawai['nama_lengkap'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control"
                        value="<?= htmlspecialchars($pegawai['nip'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-control"
                        value="<?= htmlspecialchars($pegawai['jabatan'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($pegawai['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No HP</label>
                    <input type="text" name="no_hp" class="form-control"
                        value="<?= htmlspecialchars($pegawai['no_hp'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Upload Foto Baru</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5 py-2">
                        <i class="bi bi-check-circle me-2"></i>Simpan Profil
                    </button>
                </div>
            </form>

            <hr class="my-4">

            <!-- Data Tersimpan -->
            <?php if ($pegawai): ?>
            <h5 class="mb-3 section-heading"><i class="bi bi-info-circle me-2"></i>Data Pegawai</h5>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <th width="200">Nama Lengkap</th>
                            <td><?= htmlspecialchars($pegawai['nama_lengkap']) ?></td>
                        </tr>
                        <tr>
                            <th>NIP</th>
                            <td><?= htmlspecialchars($pegawai['nip']) ?></td>
                        </tr>
                        <tr>
                            <th>Jabatan</th>
                            <td><?= htmlspecialchars($pegawai['jabatan']) ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($pegawai['email']) ?></td>
                        </tr>
                        <tr>
                            <th>No HP</th>
                            <td><?= htmlspecialchars($pegawai['no_hp']) ?></td>
                        </tr>
                        <tr>
                            <th>Foto</th>
                            <td>
                                <?php if (!empty($pegawai['foto'])): ?>
                                    <img src="../uploads/<?= htmlspecialchars($pegawai['foto']); ?>" width="120" class="rounded shadow">
                                <?php else: ?>
                                    <span class="text-muted">Belum ada foto</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

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

    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Particles.js configuration
    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 80,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#4cc9f0"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                }
            },
            "opacity": {
                "value": 0.3,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#4361ee",
                "opacity": 0.2,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 2,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "grab"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 140,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 200,
                    "duration": 0.4
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true
    });
});
</script>
</body>
</html>