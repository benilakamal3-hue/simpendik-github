<?php
session_start();
require_once '../db_config.php';

// Mock user jika belum login
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'nama_lengkap' => 'Pegawai',
        'nip' => '123456789',
        'jabatan' => 'Staff Administrasi',
    ];
}

$user = $_SESSION['user'];

// Ambil data user
$nip = $user['nip'] ?? '';
$nama_lengkap = $user['nama_lengkap'] ?? '';

// Ambil data SKP dari database
$data = [];
$stmt = $conn->prepare("SELECT * FROM skp WHERE nip = ? ORDER BY tahun DESC, bulan DESC");
$stmt->bind_param("s", $nip);
$stmt->execute();

$stmt->close();

// Daftar nama bulan
$bulan = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$now = date('n');
$current_year = date('Y');

// Generate list of years (5 years back and 1 year forward)
$years = range($current_year - 5, $current_year + 1);
rsort($years);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['lampiran'])) {
    $target_dir = "../uploads/skp/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = basename($_FILES['lampiran']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['error'] = "Hanya file PDF, DOC, JPG, PNG yang diperbolehkan.";
    } elseif ($_FILES['lampiran']['size'] > $max_size) {
        $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 5MB.";
    } else {
        $new_file_name = "SKP_" . $nip . "_" . date('YmdHis') . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $target_file)) {
            // Simpan data ke database
            $stmt = $conn->prepare("INSERT INTO skp (nip, bulan, tahun, uraian, output, keterangan, file_lampiran) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siissss", 
                $nip,
                $_POST['bulan'],
                $_POST['tahun'],
                $_POST['uraian'],
                $_POST['output'],
                $_POST['keterangan'],
                $new_file_name
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "SKP berhasil disimpan dengan lampiran file.";
                header("Location: skp.php");
                exit();
            } else {
                $_SESSION['error'] = "Gagal menyimpan data SKP: " . $stmt->error;
                unlink($target_file); // Hapus file yang sudah diupload
            }
        } else {
            $_SESSION['error'] = "Maaf, terjadi kesalahan saat mengupload file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SKP & Laporan Kinerja - Monev Pegawai</title>
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

        /* SKP Form Styles */
        .skp-card {
            background: rgba(31, 41, 55, 0.6);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            padding: 30px;
            margin-bottom: 30px;
        }

        .skp-header {
            border-bottom: 2px solid var(--accent);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(76, 201, 240, 0.25);
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* File Upload Styles */
        .file-upload {
            position: relative;
            overflow: hidden;
        }

        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }

        .file-preview {
            display: none;
            margin-top: 10px;
        }

        .file-preview img {
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Table Styles */
        .skp-table {
            background: rgba(31, 41, 55, 0.6);
            border-radius: 10px;
            overflow: hidden;
        }

        .skp-table th {
            background-color: var(--primary);
            color: white;
        }

        .skp-table td {
            vertical-align: middle;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .skp-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .skp-table tr:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        /* Buttons */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Badges */
        .badge-file {
            background-color: rgba(76, 201, 240, 0.2);
            color: var(--accent);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        /* Responsive */
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
        <a class="navbar-brand d-flex align-items-center" href="../dashboard.php">
            <img src="logo2.png" alt="Logo Dinas" style="height: 40px; margin-right: 10px;">
            <span class="d-none d-sm-inline">Monitoring & Evaluasi</span>
        </a>
        <div class="d-flex align-items-center">
            <div class="user-greeting me-3">
                <span id="greeting-text"></span>
                <strong><?= htmlspecialchars($nama_lengkap) ?></strong>
                <small class="d-block text-muted"> <?= htmlspecialchars($nip) ?></small>
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
    <a href="laporan.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Evaluasi Kinerja">
        <i class="bi bi-clipboard-check"></i>
        <span>Laporan Kinerja</span>
    </a>
    <a href="form.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Form Pengajuan">
        <i class="bi bi-file-earmark-text"></i>
        <span>Pengajuan Form</span>
    </a>
    <a href="skp.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="SKP Pegawai">
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-briefcase me-2"></i> Sasaran Kinerja Pegawai (SKP)</h2>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Form Input SKP -->
    <div class="skp-card">
        <div class="skp-header">
            <h4><i class="bi bi-plus-circle me-2"></i> Formulir SKP</h4>
            <p class="text-muted">Isi formulir berikut untuk menambahkan target kinerja bulanan</p>
        </div>

        <form id="skpForm" action="skp.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="nip" value="<?= htmlspecialchars($nip) ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama Pegawai</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($nama_lengkap) ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIP</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($nip) ?>" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bulan</label>
                    <select class="form-select" name="bulan" required>
                        <?php foreach ($bulan as $i => $b): ?>
                            <option value="<?= $i + 1 ?>" <?= ($i + 1) == $now ? 'selected' : '' ?>>
                                <?= $b ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <select class="form-select" name="tahun" required>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= $year == $current_year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Uraian Tugas</label>
                    <textarea class="form-control" name="uraian" rows="3" required placeholder="Deskripsikan tugas utama Anda bulan ini..."></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Target Output</label>
                    <textarea class="form-control" name="output" rows="2" required placeholder="Tuliskan target output/hasil yang ingin dicapai..."></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" class="form-control" name="keterangan" placeholder="Opsional">
                </div>
                <div class="col-12">
                    <label class="form-label">Lampiran Dokumen</label>
                    <div class="file-upload btn btn-outline-light w-100">
                        <span><i class="bi bi-upload me-2"></i>Pilih File</span>
                        <input type="file" name="lampiran" id="fileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                    <div class="file-preview mt-2" id="filePreview">
                        <small class="text-muted">Pratinjau:</small>
                        <div id="previewContent" class="mt-1"></div>
                    </div>
                    <small class="text-muted">Format: PDF, DOC, JPG, PNG (Maks. 5MB)</small>
                </div>
                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <i class="bi bi-send-check me-2"></i>Simpan SKP
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Daftar SKP -->
    <div class="skp-card">
        <div class="skp-header">
            <h4><i class="bi bi-list-check me-2"></i> Riwayat SKP</h4>
            <p class="text-muted">Daftar Sasaran Kinerja Pegawai yang telah Anda buat</p>
        </div>

        <div class="table-responsive">
            <table class="table skp-table">
                <thead>
                    <tr>
                        <th width="20%">Periode</th>
                        <th width="30%">Uraian Tugas</th>
                        <th width="25%">Target Output</th>
                        <th width="15%">Keterangan</th>
                        <th width="10%">Lampiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">Belum ada data SKP</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $d): ?>
                            <tr>
                                <td><?= $bulan[$d['bulan'] - 1] . ' ' . $d['tahun'] ?></td>
                                <td><?= htmlspecialchars($d['uraian']) ?></td>
                                <td><?= htmlspecialchars($d['output']) ?></td>
                                <td><?= !empty($d['keterangan']) ? htmlspecialchars($d['keterangan']) : '-' ?></td>
                                <td class="text-center">
                                    <?php if (!empty($d['file_lampiran'])): ?>
                                        <a href="../uploads/skp/<?= htmlspecialchars($d['file_lampiran']) ?>" 
                                           target="_blank" 
                                           class="badge-file" 
                                           data-bs-toggle="tooltip" 
                                           title="Lihat Lampiran">
                                            <i class="bi bi-paperclip"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

    // File upload preview
    $('#fileInput').on('change', function() {
        const file = this.files[0];
        const preview = $('#filePreview');
        const content = $('#previewContent');
        
        content.empty();
        
        if (file) {
            preview.show();
            
            if (file.type.startsWith('image/')) {
                const img = $('<img>', { 
                    class: 'img-fluid rounded', 
                    style: 'max-height: 150px;' 
                });
                img.attr('src', URL.createObjectURL(file));
                content.append(img);
            } else {
                content.append(`
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-text fs-1 me-3"></i>
                        <div>
                            <div>${file.name}</div>
                            <small class="text-muted">${(file.size / 1024).toFixed(2)} KB</small>
                        </div>
                    </div>
                `);
            }
        } else {
            preview.hide();
        }
    });

    // Form validation
    $('#skpForm').submit(function(e) {
        const uraian = $('[name="uraian"]').val().trim();
        const output = $('[name="output"]').val().trim();
        const fileInput = $('#fileInput')[0];
        
        if (uraian.length < 10 || output.length < 10) {
            alert('Uraian tugas dan target output harus diisi minimal 10 karakter');
            e.preventDefault();
            return false;
        }
        
        // Check file size if file is selected
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size;
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (fileSize > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 5MB.');
                e.preventDefault();
                return false;
            }
        }
        
        if (!confirm('Apakah Anda yakin ingin menyimpan SKP ini?')) {
            e.preventDefault();
            return false;
        }
        
        // Disable button to prevent double submit
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Menyimpan...');
        return true;
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
</body>
</html>