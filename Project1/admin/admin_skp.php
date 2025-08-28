<?php
session_start();
include '../db_config.php';

// Hanya admin yang boleh mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administrator') {
    header("Location: ../index.php");
    exit();
}

// Ambil data SKP dari database
$sql = "SELECT * FROM skp ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Data SKP Pegawai | Admin Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --bg-color: #f5f7fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 25px;
            width: 100%;
            max-width: 400px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
            box-shadow: var(--card-shadow);
        }
        
        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9e9e9e;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        tr {
            transition: background-color 0.2s ease;
        }
        
        tr:not(:first-child):hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            gap: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 2px solid var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }
        
        .badge-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: #4cc9f0;
        }
        
        .badge-danger {
            background-color: rgba(247, 37, 133, 0.1);
            color: #f72585;
        }
        
        .badge-warning {
            background-color: rgba(248, 150, 30, 0.1);
            color: #f8961e;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(67, 97, 238, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
            }
        }
        
        .floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s ease;
        }
        
        .floating-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }
        
        .tooltip {
            position: relative;
            display: inline-block;
        }
        
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -60px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        .progress-container {
            width: 100%;
            height: 8px;
            background-color: #e0e0e0;
            border-radius: 4px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 1s ease-in-out;
        }
        
        .progress-success {
            background-color: #4cc9f0;
        }
        
        .progress-warning {
            background-color: #f8961e;
        }
        
        .progress-danger {
            background-color: #f72585;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-box {
                max-width: 100%;
            }
            
            th, td {
                padding: 10px 8px;
            }
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .rotate {
            animation: rotate 2s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary);
        }
    </style>
</head>
<body>
    <div class="container animate__animated animate__fadeIn">
        <div class="header">
            <h2><i class="fas fa-chart-line"></i> Data SKP Pegawai</h2>
            <div class="actions">
                <a href="../admin_dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" onkeyup="cariPegawai()" placeholder="Cari NIP, nama, atau jabatan pegawai..." autocomplete="off">
        </div>
        
        <div class="card animate__animated animate__fadeInUp">
            <div class="table-responsive">
                <table id="skpTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-id-card"></i> NIP</th>
                            <th><i class="fas fa-user"></i> Nama Lengkap</th>
                            <th><i class="fas fa-briefcase"></i> Jabatan</th>
                            <th><i class="fas fa-calendar-alt"></i> Tahun</th>
                            <th><i class="fas fa-star"></i> Nilai SKP</th>
                            <th><i class="fas fa-info-circle"></i> Keterangan</th>
                            <th><i class="fas fa-clock"></i> Dibuat Pada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()) { 
                            $nilai = $row['nilai_skp'];
                            $badgeClass = '';
                            $progressClass = '';
                            
                            if ($nilai >= 85) {
                                $badgeClass = 'badge-success';
                                $progressClass = 'progress-success';
                            } elseif ($nilai >= 70) {
                                $badgeClass = 'badge-warning';
                                $progressClass = 'progress-warning';
                            } else {
                                $badgeClass = 'badge-danger';
                                $progressClass = 'progress-danger';
                            }
                        ?>
                            <tr class="fade-in">
                                <td><?= htmlspecialchars($row['nip']) ?></td>
                                <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                <td><?= $row['tahun'] ?></td>
                                <td>
                                    <div class="tooltip">
                                        <span class="badge <?= $badgeClass ?>"><?= $nilai ?></span>
                                        <span class="tooltiptext">Nilai SKP <?= $nilai ?></span>
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress-bar <?= $progressClass ?>" style="width: <?= $nilai ?>%"></div>
                                    </div>
                                </td>
                                <td><?= nl2br(htmlspecialchars($row['keterangan'])) ?></td>
                                <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="floating-btn pulse" id="scrollToTop" title="Kembali ke atas">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Enhanced search function with debounce
        let searchTimeout;
        function cariPegawai() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                let input = document.getElementById("searchInput").value.toLowerCase();
                let rows = document.querySelectorAll("#skpTable tbody tr");
                let hasResults = false;
                
                rows.forEach(row => {
                    let nama = row.cells[1].innerText.toLowerCase();
                    let nip = row.cells[0].innerText.toLowerCase();
                    let jabatan = row.cells[2].innerText.toLowerCase();
                    let tahun = row.cells[3].innerText.toLowerCase();
                    
                    if (nama.includes(input) || nip.includes(input) || jabatan.includes(input) || tahun.includes(input)) {
                        row.style.display = "";
                        row.classList.add('animate__animated', 'animate__fadeIn');
                        hasResults = true;
                    } else {
                        row.style.display = "none";
                    }
                });
                
                // Show no results message if needed
                const noResults = document.getElementById('noResults');
                if (!hasResults && rows.length > 0) {
                    if (!noResults) {
                        const tbody = document.querySelector("#skpTable tbody");
                        const tr = document.createElement('tr');
                        tr.id = 'noResults';
                        tr.innerHTML = `<td colspan="7" style="text-align: center; padding: 30px; color: #999;">Tidak ditemukan data yang sesuai dengan pencarian "${input}"</td>`;
                        tbody.appendChild(tr);
                    }
                } else if (noResults) {
                    noResults.remove();
                }
            }, 300);
        }
        
        // Scroll to top button
        const scrollToTopBtn = document.getElementById('scrollToTop');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.style.display = 'flex';
            } else {
                scrollToTopBtn.style.display = 'none';
            }
        });
        
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Animate table rows on load
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll("#skpTable tbody tr");
            rows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
            });
            
            // Create chart if needed (example)
            // createPerformanceChart();
        });
        
        // Example chart function
        function createPerformanceChart() {
            const ctx = document.createElement('canvas');
            ctx.id = 'performanceChart';
            document.querySelector('.card').prepend(ctx);
            
            // Sample data - you would replace this with your actual data
            const data = {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Rata-rata Nilai SKP',
                    data: [75, 78, 82, 85, 80, 88],
                    backgroundColor: 'rgba(67, 97, 238, 0.2)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            };
            
            new Chart(ctx, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 60,
                            max: 100
                        }
                    }
                }
            });
        }
        
        // Add hover effects to table rows
        const tableRows = document.querySelectorAll("#skpTable tbody tr");
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.transform = 'scale(1.01)';
                row.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            });
            
            row.addEventListener('mouseleave', () => {
                row.style.transform = '';
                row.style.boxShadow = '';
            });
        });
        
        // Add click effect to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(btn => {
            btn.addEventListener('mousedown', () => {
                btn.style.transform = 'scale(0.95)';
            });
            
            btn.addEventListener('mouseup', () => {
                btn.style.transform = '';
            });
            
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = '';
            });
        });
    </script>
</body>
</html>