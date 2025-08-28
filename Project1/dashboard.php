<?php
session_start();
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['nama_lengkap' => 'nama_lengkap', 'jabatan' => 'Staff Administrasi'];
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
    <title>Dashboard - Monev Pegawai</title>
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
            --card-bg: rgba(30, 41, 59, 0.85);
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
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        body.bg-siang {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1501785888041-af3ef285b470?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        body.bg-sore {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
        }

        body.bg-malam {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1539650116574-75c0c6d73f6e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1770&q=80');
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
            height: 100%;
            position: relative;
            z-index: 1;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            transition: var(--transition);
        }

        .card:hover .card-icon {
            transform: scale(1.2) rotate(10deg);
        }

        .card-title {
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
            color: var(--text-primary);
        }

        .card-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--accent);
            transition: width 0.3s ease;
        }

        .card:hover .card-title::after {
            width: 70px;
        }

        .card-text {
            color: var(--text-secondary) !important;
        }

        .text-muted {
            color: var(--text-muted) !important;
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
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
        .delay-6 { animation-delay: 0.6s; }

        /* Floating elements */
        .floating {
            animation: floating 6s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
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

        /* Badges */
        .badge {
            font-weight: 500;
        }

        .badge.bg-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)) !important;
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
            }
            .sidebar-toggle {
                left: 20px;
            }
        }

        /* Particles background */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Content headings */
        h2 {
            font-weight: 600;
            color: var(--text-primary);
            position: relative;
            padding-bottom: 10px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 3px;
        }

        /* Stats cards */
        .stats-card {
            text-align: center;
            padding: 20px;
        }

        .stats-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
            color: var(--text-primary);
        }

        .stats-card h6 {
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Menu cards */
        .menu-card {
            padding: 25px 20px;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .menu-card:hover {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-<?php echo $time_of_day; ?>">
<!-- Particles Background -->
<div id="particles-js"></div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="#">
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
                    <li><a class="dropdown-item" href="pages/profil.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
    <a href="dashboard.php" class="active" data-bs-toggle="tooltip" data-bs-placement="right" title="Beranda">
        <i class="bi bi-house-fill"></i>
        <span>Dashboard</span>
    </a>
    <a href="pages/laporan.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Evaluasi Kinerja">
        <i class="bi bi-clipboard-check"></i>
        <span>Laporan Kinerja</span>
    </a>
    <a href="pages/form.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Form Pengajuan">
        <i class="bi bi-journal-text"></i>
        <span>Pengajuan Form</span>
    </a>
    <a href="pages/skp.php" data-bs-toggle="tooltip" data-bs-placement="right" title="SKP Pegawai">
        <i class="bi bi-briefcase"></i>
        <span>SKP Pegawai</span>
    </a>
      <a href="pages/arsip.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Arsip Digital">
        <i class="bi bi-folder"></i>
        <span>Arsip Digital</span>
    </a>
    <a href="pages/absensi.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Absensi Pegawai">
        <i class="bi bi-geo-alt-fill"></i>
        <span>Absensi</span>
    </a>
    <a href="pages/profil.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Profil Pegawai">
        <i class="bi bi-person-lines-fill"></i>
        <span>Profil Pegawai</span>
    </a>
        <a href="pages/riwayat_hidup.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Profil Pegawai">
        <i class="bi bi-file-text"></i>
        <span>Riwayat Hidup</span>
    </a>

</div>

<!-- Main Content -->
<div class="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard</h2>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2" id="time-badge"></span>
                <span class="badge bg-secondary"><?php echo date('d F Y'); ?></span>
            </div>
        </div>
        
        <!-- Menu Cards -->
        <h3 class="mb-4">Akses Cepat</h3>
        <div class="row g-4">
            <?php
            $menus = [
                ["laporan.php", "Laporan Kerja", "bi-clipboard-check", "Isi dan Lihat kinerja bulanan", "primary"],
                ["form.php", "Pengajuan Form", "bi-journal-text", "Ajukan cuti, izin, atau tugas dinas luar", "success"],
                ["skp.php", "SKP Pegawai", "bi-briefcase", "Sasaran Kinerja Pegawai dan progres pencapaian", "warning"],
                ["notifikasi.php", "Notifikasi", "bi-bell", "Pesan dan pemberitahuan penting sistem", "danger"],
                ["arsip.php", "Arsip Digital", "bi-folder", "Dokumen & laporan terdigitalisasi", "info"],
                ["absensi.php", "Absensi", "bi-geo-alt-fill", "Presensi masuk & keluar dengan verifikasi GPS", "secondary"],
                ["profil.php", "Profil Pegawai", "bi-person-lines-fill", "Kelola data pribadi dan preferensi", "dark"],
                ["riwayat_hidup.php", "Riwayat Hidup", "bi bi-file-text", "Kelola data riwayat hidup", "yellow"],
            ];
            
            foreach ($menus as $index => $menu) {
                echo '
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="pages/'.$menu[0].'" class="text-decoration-none">
                        <div class="card fade-in delay-'.($index+1).'">
                            <div class="card-body menu-card">
                                <i class="bi '.$menu[2].' card-icon mb-3 floating"></i>
                                <h5 class="card-title">'.$menu[1].'</h5>
                                <p class="card-text">'.$menu[3].'</p>
                                <div class="mt-3">
                                    <span class="badge bg-'.$menu[4].'">Akses Cepat</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>';
            }
            ?>
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

    // Dynamic greeting and time badge
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
    
    sidebarToggle.addEventListener('click', function() {
        document.body.classList.toggle('sidebar-collapsed');
    });

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

    // Animate cards on scroll
    const animateOnScroll = function() {
        const cards = document.querySelectorAll('.fade-in');
        cards.forEach((card) => {
            const cardPosition = card.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (cardPosition < screenPosition) {
                card.classList.add('show');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Initialize
});
</script>
</body>
</html>