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
    <title>Pengajuan Form - Monev Pegawai</title>
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
        .form-control, .form-select, .form-textarea {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: var(--text-primary);
            border-radius: var(--border-radius);
            padding: 12px 15px;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus, .form-textarea:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(76, 201, 240, 0.25);
            color: var(--text-primary);
        }

        .form-control::placeholder, .form-textarea::placeholder {
            color: var(--text-muted);
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .required::after {
            content: " *";
            color: var(--danger);
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

        /* File preview */
        .file-preview {
            display: none;
            margin-top: 10px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .file-preview img {
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
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
                    <i class="bi bi-file-earmark-text text-white"></i>
                </div>
                <span class="d-none d-sm-inline">Monitoring & Evaluasi</span>
            </a>
            <div class="d-flex align-items-center">
                <div class="user-greeting me-3">
                    <span id="greeting-text"></span>
                    <strong><?= htmlspecialchars($user['nama_lengkap'] ?? 'Pengguna') ?></strong>
                    <small class="d-block text-muted"><?= htmlspecialchars($user['jabatan'] ?? '-') ?></small>
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
        <a href="form.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Form Pengajuan">
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
                <h2><i class="bi bi-file-earmark-text me-2"></i> Form Pengajuan Digital</h2>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2" id="time-badge"></span>
                    <span class="badge bg-secondary"><?php echo date('d F Y'); ?></span>
                </div>
            </div>

            <!-- Submission Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Formulir Pengajuan</h5>
                    <p class="text-muted mb-0">Isi form berikut untuk membuat pengajuan baru</p>
                </div>
                <div class="card-body">
                    <form id="pengajuanForm" action="proses_pengajuan.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <!-- Employee Info -->
                            <div class="col-md-4">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">NIP</label>
                                <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($user['nip'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Jabatan</label>
                                <input type="text" name="jabatan" class="form-control" value="<?= htmlspecialchars($user['jabatan'] ?? '-') ?>" readonly>
                            </div>

                            <!-- Submission Details -->
                            <div class="col-md-4">
                                <label class="form-label required">Jenis Pengajuan</label>
                                <select name="jenis" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Jenis --</option>
                                    <option value="Cuti">Cuti</option>
                                    <option value="Izin">Izin</option>
                                    <option value="Tugas Luar">Tugas Luar</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" required>
                            </div>

                            <!-- Additional Info -->
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control form-textarea" rows="3" placeholder="Jelaskan tujuan pengajuan ini..."></textarea>
                            </div>

                            <!-- File Attachment -->
                            <div class="col-md-6">
                                <label class="form-label">Lampiran Dokumen</label>
                                <input type="file" name="lampiran" class="form-control" id="fileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="file-preview mt-2" id="filePreview">
                                    <small class="text-muted">Pratinjau:</small>
                                    <div id="previewContent" class="mt-1"></div>
                                </div>
                                <small class="text-muted">Format: PDF, DOC, JPG, PNG (Maks. 5MB)</small>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-send-check me-2"></i>Kirim Pengajuan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Dynamic greeting
        const greetingEl = document.getElementById("greeting-text");
        const timeBadge = document.getElementById("time-badge");
        const now = new Date();
        const hour = now.getHours();
        let greet = "";
        let timeText = "";

        if (hour >= 5 && hour < 11) {
            greet = "Selamat Pagi, ";
            timeText = "Pagi";
        } else if (hour >= 11 && hour < 15) {
            greet = "Selamat Siang, ";
            timeText = "Siang";
        } else if (hour >= 15 && hour < 19) {
            greet = "Selamat Sore, ";
            timeText = "Sore";
        } else {
            greet = "Selamat Malam, ";
            timeText = "Malam";
        }

        if (greetingEl) greetingEl.textContent = greet;
        if (timeBadge) timeBadge.textContent = timeText;

        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
            });
        }

        // File preview
        $('#fileInput').on('change', function() {
            const file = this.files && this.files[0] ? this.files[0] : null;
            const preview = $('#filePreview');
            const content = $('#previewContent');
            content.empty();
            if (file) {
                preview.show();
                if (file.type && file.type.startsWith('image/')) {
                    const img = $('<img>', { class: 'img-fluid rounded', style: 'max-height: 150px;' });
                    img.attr('src', URL.createObjectURL(file));
                    content.append(img);
                } else {
                    content.append($('<div>', {
                        class: 'd-flex align-items-center',
                        html: `
                            <i class="bi bi-file-earmark-text fs-1 me-3"></i>
                            <div>
                                <div>${file.name}</div>
                                <small class="text-muted">${(file.size / 1024).toFixed(2)} KB</small>
                            </div>
                        `
                    }));
                }
            } else {
                preview.hide();
            }
        });

        // Form submission
        $('#pengajuanForm').on('submit', function(e) {
            if (!confirm('Apakah Anda yakin ingin mengirim pengajuan ini?')) {
                e.preventDefault();
                return;
            }
            const btn = $('#submitBtn');
            btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...`);
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
    </script>
</body>
</html>