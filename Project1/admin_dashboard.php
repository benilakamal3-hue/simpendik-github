<?php
session_start();
include 'db_config.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administrator') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --accent: #1abc9c;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f5f7fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* HEADER */
        .header {
            background: linear-gradient(135deg, var(--secondary), var(--dark));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
            box-shadow: var(--shadow);
            z-index: 10;
            animation: gradientBG 15s ease infinite;
            background-size: 400% 400%;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .header small {
            font-size: 0.9rem;
            opacity: 0.9;
            display: inline-block;
            margin-top: 5px;
        }

        /* MENU */
        .menu {
            background: var(--accent);
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 12px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 5;
        }
        .menu a {
            color: white;
            text-decoration: none;
            padding: 8px 18px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 30px;
            transition: var(--transition);
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .menu a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .menu a i {
            font-size: 1rem;
        }
        .menu a.logout {
            background: var(--danger);
        }
        .menu a.logout:hover {
            background: #c0392b;
        }

        /* CONTENT */
        .content {
            padding: 25px;
            flex: 1;
            padding-bottom: 80px; /* space for footer */
        }
        .content h2 {
            color: var(--dark);
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .content h2 i {
            color: var(--accent);
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: none;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--accent);
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .card h3 {
            margin-bottom: 15px;
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }
        .card .count {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--secondary);
            margin: 10px 0;
            position: relative;
        }
        .card .icon {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 15px;
            opacity: 0.8;
        }

        /* FOOTER */
        footer {
            background: var(--secondary);
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 0.85rem;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            margin-top: auto;
        }
        footer a {
            color: var(--accent);
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: underline;
        }

        /* JAM REALTIME */
        #clock {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 15px;
            border-radius: 30px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #clock i {
            font-size: 1rem;
        }

        /* NOTIFICATION BELL */
        .notification-bell {
            position: absolute;
            top: 20px;
            left: 20px;
            cursor: pointer;
            font-size: 1.2rem;
            color: white;
            background: rgba(255, 255, 255, 0.15);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .notification-bell:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.1);
        }
        .notification-bell .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* CHART CONTAINER */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: var(--shadow);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }
            .cards {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            #clock {
                position: static;
                margin-top: 15px;
                display: inline-flex;
            }
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .card:nth-child(5) { animation-delay: 0.5s; }
        .card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>

<div class="header">
    <div class="notification-bell" id="notificationBell">
        <i class="bi bi-bell-fill"></i>
        <div class="badge" id="notificationBadge"></div>
    </div>
    <h1>Admin Dashboard</h1>
    <small>Selamat datang, <?php echo $_SESSION['user']['nama_lengkap']; ?> (Administrator)</small>
    <div id="clock">
        <i class="bi bi-clock"></i>
        <span id="clockTime"></span>
    </div>
</div>

<div class="menu">
    <a href="admin/admin_laporan.php" data-tooltip="Form Laporan Kinerja"><i class="bi bi-journal-text"></i> Laporan Kinerja</a>
    <a href="admin/admin_pengajuan.php" data-tooltip="Form Pengajuan"><i class="bi bi-journal-text"></i> Pengajuan</a>
    <a href="admin/admin_skp.php" data-tooltip="SKP Pegawai"><i class="bi bi-briefcase"></i> SKP</a>
    <a href="admin/admin_absensi.php" data-tooltip="Absensi Pegawai"><i class="bi bi-geo-alt-fill"></i> Absensi</a>
    <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="content">
    <h2><i class="bi bi-speedometer2"></i> Ringkasan Data</h2>
    <div class="cards">
        <?php
        $tables = [
            'laporan_kinerja' => ['laporan_kinerja', 'bi bi-people'],
            'pegawai' => ['Pegawai', 'bi bi-people'],
            'pengajuan' => ['Pengajuan', 'bi bi-file-text'],
            'presensi' => ['Presensi', 'bi bi-calendar-check'],
            'skp' => ['SKP', 'bi bi-award'],
        ];

        foreach ($tables as $table => $data) {
            $label = $data[0];
            $icon = $data[1];
            $res = $conn->query("SELECT COUNT(*) as total FROM $table");
            $row = $res->fetch_assoc();
            $total = $row['total'] ?? 0;
            echo "
            <div class='card'>
                <div class='icon'><i class='$icon'></i></div>
                <h3>$label</h3>
                <div class='count' data-count='$total'>0</div>
            </div>";
        }
        ?>
    </div>

    <div class="chart-container">
        <h3><i class="bi bi-bar-chart-line"></i> Statistik Bulan Ini</h3>
        <canvas id="statsChart"></canvas>
    </div>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Aplikasi Monitoring & Evaluasi Pegawai - Beny Lakamal - 082236953573
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Jam Realtime dengan Tanggal
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const time = now.toLocaleDateString('id-ID', options);
        document.getElementById('clockTime').textContent = time;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Animasi Angka Count dengan Easing
    document.querySelectorAll('.count').forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const duration = 2000; // 2 seconds
        const start = 0;
        const startTime = performance.now();
        
        const animateCount = (currentTime) => {
            const elapsedTime = currentTime - startTime;
            const progress = Math.min(elapsedTime / duration, 1);
            // Easing function (easeOutQuad)
            const easedProgress = 1 - Math.pow(1 - progress, 3);
            const currentCount = Math.floor(easedProgress * target);
            
            counter.textContent = currentCount.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animateCount);
            } else {
                counter.textContent = target.toLocaleString();
            }
        };
        
        requestAnimationFrame(animateCount);
    });

    // Tooltip untuk menu
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = e.target.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width/2 - tooltip.offsetWidth/2}px`;
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10}px`;
            
            e.target.addEventListener('mouseleave', () => {
                tooltip.remove();
            }, { once: true });
        });
    });

    // Style untuk tooltip
    const tooltipStyle = document.createElement('style');
    tooltipStyle.textContent = `
        .tooltip {
            position: absolute;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            pointer-events: none;
            z-index: 1000;
            transform: translateY(5px);
            opacity: 0;
            transition: all 0.2s ease;
        }
        [data-tooltip]:hover .tooltip {
            transform: translateY(0);
            opacity: 1;
        }
    `;
    document.head.appendChild(tooltipStyle);

    // Notifikasi Bell Animation
    const notificationBell = document.getElementById('notificationBell');
    notificationBell.addEventListener('click', () => {
        // Rotate and shake animation
        notificationBell.style.transform = 'rotate(15deg)';
        setTimeout(() => {
            notificationBell.style.transform = 'rotate(-15deg)';
            setTimeout(() => {
                notificationBell.style.transform = 'rotate(0)';
            }, 150);
        }, 150);
        
        // Show notification dropdown (simulated)
        alert('Anda memiliki 3 notifikasi baru');
    });

    // Chart.js Implementation
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('statsChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['laporan', 'Pengajuan', 'SKP', 'Presensi', 'Notifikasi'],
                datasets: [{
                    label: 'Data Bulan Ini',
                    data: [12, 19, 8, 15, 7],
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(241, 196, 15, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderColor: [
                        'rgba(52, 152, 219, 1)',
                        'rgba(46, 204, 113, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(241, 196, 15, 1)',
                        'rgba(231, 76, 60, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    });

    // Background particles effect
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.createElement('canvas');
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '-1';
        document.body.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const particles = [];
        const particleCount = 50;
        
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                size: Math.random() * 3 + 1,
                speedX: Math.random() * 0.5 - 0.25,
                speedY: Math.random() * 0.5 - 0.25,
                color: `rgba(26, 188, 156, ${Math.random() * 0.3 + 0.1})`
            });
        }
        
        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];
                
                p.x += p.speedX;
                p.y += p.speedY;
                
                if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
                if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
                
                ctx.fillStyle = p.color;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fill();
            }
            
            requestAnimationFrame(animateParticles);
        }
        
        animateParticles();
        
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    });
</script>

</body>
</html>