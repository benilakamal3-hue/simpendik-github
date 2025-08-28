<?php
session_start();
include '../db_config.php';

// Hanya admin yang bisa mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administrator') {
    header("Location: ../index.php");
    exit();
}

// Ambil pencarian
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";

$sql = "SELECT a.id, p.nama_lengkap, a.tanggal, a.jam_masuk, a.jam_keluar, a.jenis
        FROM presensi a
        JOIN pegawai p ON a.pegawai_id = p.id
        WHERE p.nama_lengkap LIKE '%$search%' OR p.id LIKE '%$search%'
        ORDER BY a.tanggal DESC";
        
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 Absensi Pegawai | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-color);
            color: var(--dark);
            line-height: 1.6;
        }
        
        .navbar-brand {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-card {
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            border: none;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }
        
        .search-card:hover {
            transform: translateY(-3px);
        }
        
        .search-card .card-body {
            padding: 1.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-success:hover {
            background-color: #3aa8d8;
            border-color: #3aa8d8;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            padding: 0;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary);
            color: white;
            border-bottom: none;
            padding: 15px;
            font-weight: 500;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: scale(1.005);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-hadir {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .status-terlambat {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        
        .status-tanpa-keterangan {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        .status-cuti {
            background-color: rgba(72, 149, 239, 0.1);
            color: var(--info);
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
        
        .time-cell {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        .fade-in-row {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
        
        .empty-state {
            padding: 50px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container py-4 animate__animated animate__fadeIn">
        <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 mb-4 shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-clipboard-list"></i> Absensi Pegawai
                </a>
                <div class="d-flex align-items-center">
                    <a href="../admin_dashboard.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </nav>

        <div class="search-card animate__animated animate__fadeInUp">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari nama pegawai atau ID..." value="<?= htmlspecialchars($search) ?>" id="searchInput">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Cari
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end">
                        <a href="cetak_absensi.php?search=<?= urlencode($search) ?>" target="_blank" class="btn btn-success ms-2">
                            <i class="fas fa-print me-1"></i> Cetak Rekapan
                        </a>
                        <button type="button" class="btn btn-info ms-2" id="refreshBtn">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-container animate__animated animate__fadeInUp">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0) {
                            $no = 1;
                            while($row = $result->fetch_assoc()) {
                                $statusClass = '';
                                switch(strtolower($row['jenis'])) {
                                    case 'hadir': $statusClass = 'jenis-hadir'; break;
                                    case 'terlambat': $statusClass = 'jenis-terlambat'; break;
                                    case 'cuti': $statusClass = 'jenis-cuti'; break;
                                    default: $statusClass = '';
                                }
                                
                                echo "<tr class='fade-in-row' style='animation-delay: ".($no*0.05)."s'>
                                    <td>{$no}</td>
                                    <td>{$row['nama_lengkap']}</td>
                                    <td>{$row['tanggal']}</td>
                                    <td class='time-cell'>{$row['jam_masuk']}</td>
                                    <td class='time-cell'>{$row['jam_keluar']}</td>
                                    <td><span class='jenis-badge {$statusClass}'>{$row['jenis']}</span></td>
                                </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr>
                                <td colspan='7'>
                                    <div class='empty-state'>
                                        <i class='fas fa-clipboard-list'></i>
                                        <h4>Data tidak ditemukan</h4>
                                        <p>Tidak ada data absensi yang sesuai dengan pencarian Anda</p>
                                    </div>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="floating-btn pulse" id="scrollToTop" title="Kembali ke atas">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced UI interactions
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Refresh button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                this.classList.add('rotate');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });
            
            // Highlight search term in table
            const searchTerm = "<?= htmlspecialchars($search) ?>";
            if (searchTerm) {
                highlightSearchTerm(searchTerm);
            }
            
            // Add animation to table rows on hover
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', () => {
                    row.style.boxShadow = '0 5px 15px rgba(0,0,0,0.05)';
                });
                
                row.addEventListener('mouseleave', () => {
                    row.style.boxShadow = 'none';
                });
            });
            
            // Real-time search suggestions (debounced)
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length > 2) {
                        // In a real implementation, you might fetch suggestions here
                        console.log('Searching for:', this.value);
                    }
                }, 300);
            });
            
            // Focus search input on page load
            searchInput.focus();
        });
        
        function highlightSearchTerm(term) {
            if (!term) return;
            
            const tableCells = document.querySelectorAll('tbody td');
            const regex = new RegExp(term, 'gi');
            
            tableCells.forEach(cell => {
                const originalContent = cell.textContent;
                const highlightedContent = originalContent.replace(regex, match => 
                    `<span class="highlight" style="background-color: #FFF9C4; padding: 2px; border-radius: 3px;">${match}</span>`
                );
                
                if (highlightedContent !== originalContent) {
                    cell.innerHTML = highlightedContent;
                }
            });
        }
        
        // Add animation to buttons on click
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
        
        // Add tooltips to status badges
        const statusBadges = document.querySelectorAll('.status-badge');
        statusBadges.forEach(badge => {
            badge.addEventListener('mouseenter', function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'status-tooltip';
                tooltip.textContent = this.textContent === 'Hadir' ? 'Pegawai hadir tepat waktu' : 
                                     this.textContent === 'Terlambat' ? 'Pegawai terlambat hadir' : 
                                     this.textContent === 'Tanpa Keterangan' ? 'Pegawai tidak memberikan keterangan' : 
                                     'Pegawai sedang cuti';
                tooltip.style.position = 'absolute';
                tooltip.style.backgroundColor = 'rgba(0,0,0,0.8)';
                tooltip.style.color = 'white';
                tooltip.style.padding = '5px 10px';
                tooltip.style.borderRadius = '4px';
                tooltip.style.fontSize = '12px';
                tooltip.style.zIndex = '1000';
                tooltip.style.top = (this.getBoundingClientRect().top - 30) + 'px';
                tooltip.style.left = (this.getBoundingClientRect().left + this.offsetWidth/2 - 50) + 'px';
                tooltip.style.width = '100px';
                tooltip.style.textAlign = 'center';
                
                document.body.appendChild(tooltip);
                
                badge.addEventListener('mouseleave', function() {
                    document.body.removeChild(tooltip);
                }, { once: true });
            });
        });
    </script>
</body>
</html>