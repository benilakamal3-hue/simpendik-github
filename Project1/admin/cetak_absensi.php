<?php
session_start();
include '../db_config.php';

// Hanya admin yang bisa mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administrator') {
    header("Location: ../index.php");
    exit();
}

// Ambil data pegawai untuk dropdown
$pegawai_sql = "SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap";
$pegawai_result = $conn->query($pegawai_sql);

// Ambil parameter pencarian
$pegawai_id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : "";
$bulan = isset($_GET['bulan']) ? $conn->real_escape_string($_GET['bulan']) : date('m');
$tahun = isset($_GET['tahun']) ? $conn->real_escape_string($_GET['tahun']) : date('Y');

// Query data absensi jika parameter ada
if ($pegawai_id && $bulan && $tahun) {
    $sql = "SELECT a.tanggal, a.jam_masuk, a.jam_keluar, a.status, a.keterangan 
            FROM presensi a
            WHERE a.id = '$pegawai_id'
            AND MONTH(a.tanggal) = '$bulan'
            AND YEAR(a.tanggal) = '$tahun'
            ORDER BY a.tanggal";
    
    $result = $conn->query($sql);
    $pegawai_data = $conn->query("SELECT nama, jabatan FROM pegawai WHERE id_pegawai = '$pegawai_id'")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekapan Absensi Pegawai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-hadir {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .status-terlambat {
            background-color: rgba(248, 150, 30, 0.1);
            color: #f8961e;
        }
        
        .status-tanpa-keterangan {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }
        
        .status-cuti {
            background-color: rgba(72, 149, 239, 0.1);
            color: #4895ef;
        }
        
        .print-header {
            display: none;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .print-header {
                display: block;
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            th {
                background-color: #f2f2f2;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="card mb-4 no-print">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-print me-2"></i>Cetak Rekapan Absensi</h5>
            </div>
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-6">
                        <label for="pegawai_id" class="form-label">Pilih Pegawai</label>
                        <select class="form-select" id="pegawai_id" name="pegawai_id" required>
                            <option value="">-- Pilih Pegawai --</option>
                            <?php while($row = $pegawai_result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" <?= $pegawai_id == $row['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_lengkap']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select class="form-select" id="bulan" name="bulan" required>
                            <?php for($i=1; $i<=12; $i++): ?>
                                <option value="<?= sprintf('%02d', $i) ?>" <?= $bulan == sprintf('%02d', $i) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select class="form-select" id="tahun" name="tahun" required>
                            <?php for($i=date('Y'); $i>=date('Y')-5; $i--): ?>
                                <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Tampilkan Data
                        </button>
                        <?php if(isset($result) && $result->num_rows > 0): ?>
                            <button type="button" class="btn btn-success ms-2" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Cetak
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if(isset($result) && $result->num_rows > 0): ?>
        <div class="card print-section">
            <div class="card-body">
                <div class="print-header">
                    <h4>REKAPAN ABSENSI PEGAWAI</h4>
                    <h5><?= strtoupper($pegawai_data['nama']) ?></h5>
                    <p>Periode: <?= date('F Y', strtotime("$tahun-$bulan-01")) ?></p>
                </div>
                
                <div class="d-flex justify-content-between mb-3 no-print">
                    <h5>Rekapan Absensi</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Cetak
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $total_hari = 0;
                            $total_hadir = 0;
                            $total_terlambat = 0;
                            $total_tanpa_keterangan = 0;
                            $total_cuti = 0;
                            
                            while($row = $result->fetch_assoc()): 
                                $statusClass = '';
                                switch(strtolower($row['status'])) {
                                    case 'hadir': 
                                        $statusClass = 'status-hadir';
                                        $total_hadir++;
                                        break;
                                    case 'terlambat': 
                                        $statusClass = 'status-terlambat';
                                        $total_terlambat++;
                                        break;
                                    case 'tanpa keterangan': 
                                        $statusClass = 'status-tanpa-keterangan';
                                        $total_tanpa_keterangan++;
                                        break;
                                    case 'cuti': 
                                        $statusClass = 'status-cuti';
                                        $total_cuti++;
                                        break;
                                }
                                $total_hari++;
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= $row['jam_masuk'] ?: '-' ?></td>
                                <td><?= $row['jam_keluar'] ?: '-' ?></td>
                                <td><span class="status-badge <?= $statusClass ?>"><?= $row['status'] ?></span></td>
                                <td><?= $row['keterangan'] ?: '-' ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th><?= $total_hari ?> Hari</th>
                                <th colspan="3">
                                    Hadir: <?= $total_hadir ?> | 
                                    Terlambat: <?= $total_terlambat ?> | 
                                    Tanpa Keterangan: <?= $total_tanpa_keterangan ?> | 
                                    Cuti: <?= $total_cuti ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="row mt-4 no-print">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Statistik Absensi</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="attendanceChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Informasi Pegawai</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <p class="fw-bold"><?= htmlspecialchars($pegawai_data['nama']) ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <p class="fw-bold"><?= htmlspecialchars($pegawai_data['jabatan']) ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Periode</label>
                                    <p class="fw-bold"><?= date('F Y', strtotime("$tahun-$bulan-01")) ?></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Total Hari Kerja</label>
                                    <p class="fw-bold"><?= $total_hari ?> Hari</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif(isset($_GET['pegawai_id'])): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-circle me-2"></i> Tidak ada data absensi yang ditemukan untuk kriteria yang dipilih.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        <?php if(isset($result) && $result->num_rows > 0): ?>
        // Pie chart untuk statistik absensi
        const ctx = document.getElementById('attendanceChart');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Hadir', 'Terlambat', 'Tanpa Keterangan', 'Cuti'],
                datasets: [{
                    data: [<?= $total_hadir ?>, <?= $total_terlambat ?>, <?= $total_tanpa_keterangan ?>, <?= $total_cuti ?>],
                    backgroundColor: [
                        '#4cc9f0',
                        '#f8961e',
                        '#f72585',
                        '#4895ef'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Fitur untuk memastikan form diisi sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!document.getElementById('pegawai_id').value) {
                e.preventDefault();
                alert('Silakan pilih pegawai terlebih dahulu');
            }
        });

        // Auto submit saat dropdown berubah (opsional)
        document.getElementById('bulan').addEventListener('change', function() {
            if (document.getElementById('pegawai_id').value) {
                document.querySelector('form').submit();
            }
        });

        document.getElementById('tahun').addEventListener('change', function() {
            if (document.getElementById('pegawai_id').value) {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>