<?php
session_start();
include 'db_config.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administrator') {
    header("Location: index.php");
    exit();
}

// Query untuk mendapatkan data laporan kinerja
$query = "SELECT lk.*, p.nama_lengkap 
          FROM laporan_kinerja lk 
          JOIN pegawai p ON lk.id = p.id 
          ORDER BY lk.tanggal_update DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kinerja Pegawai</title>
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
        .menu a.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
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

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: var(--accent);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
        }
        tr:hover {
            background-color: rgba(26, 188, 156, 0.05);
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }
        .btn-view {
            background-color: var(--primary);
            color: white;
        }
        .btn-view:hover {
            background-color: #2980b9;
        }
        .btn-edit {
            background-color: var(--warning);
            color: white;
        }
        .btn-edit:hover {
            background-color: #e67e22;
        }
        .btn-delete {
            background-color: var(--danger);
            color: white;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        .form-group input, .form-group select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-filter {
            background-color: var(--accent);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        .btn-filter:hover {
            background-color: #16a085;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }
        .close-modal:hover {
            color: var(--danger);
        }
        .modal-title {
            margin-bottom: 20px;
            color: var(--dark);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 10px;
        }
        .detail-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: 600;
            color: var(--dark);
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

        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }
            #clock {
                position: static;
                margin-top: 15px;
                display: inline-flex;
            }
            .detail-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Laporan Kinerja Pegawai</h1>
    <small>Selamat datang, <?php echo $_SESSION['user']['nama_lengkap']; ?> (Administrator)</small>
    <div id="clock">
        <i class="bi bi-clock"></i>
        <span id="clockTime"></span>
    </div>
</div>

<div class="menu">
    <a href="../admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="admin_laporan.php" class="active"><i class="bi bi-journal-text"></i> Laporan Kinerja</a>
    <a href="admin_pengajuan.php"><i class="bi bi-journal-text"></i> Pengajuan</a>
    <a href="admin_skp.php"><i class="bi bi-briefcase"></i> SKP</a>
    <a href="admin_absensi.php"><i class="bi bi-geo-alt-fill"></i> Absensi</a>
    <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="content">
    <h2><i class="bi bi-journal-text"></i> Data Laporan Kinerja</h2>
    
    <div class="filter-section">
        <form class="filter-form" method="GET" action="">
            <div class="form-group">
                <label for="pegawai">Pegawai</label>
                <select id="pegawai" name="pegawai">
                    <option value="">Semua Pegawai</option>
                    <?php
                    $pegawai_query = $conn->query("SELECT id, nama_lengkap FROM pegawai ORDER BY nama_lengkap");
                    while ($row = $pegawai_query->fetch_assoc()) {
                        $selected = (isset($_GET['pegawai']) && $_GET['pegawai'] == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['nama_lengkap']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="end_date">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Filter</button>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Pegawai</th>
                    <th>Aktivitas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Build filter query
                $where = [];
                $params = [];
                
                if (isset($_GET['pegawai']) && !empty($_GET['pegawai'])) {
                    $where[] = "lk.id_pegawai = ?";
                    $params[] = $_GET['pegawai'];
                }
                
                if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    $where[] = "lk.tanggal >= ?";
                    $params[] = $_GET['start_date'];
                }
                
                if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                    $where[] = "lk.tanggal <= ?";
                    $params[] = $_GET['end_date'];
                }
                
                // Apply filters if any
                if (!empty($where)) {
                    $query = "SELECT lk.*, p.nama_lengkap 
                              FROM laporan_kinerja lk 
                              JOIN pegawai p ON lk.id_pegawai = p.id 
                              WHERE " . implode(" AND ", $where) . "
                              ORDER BY lk.tanggal DESC";
                              
                    $stmt = $conn->prepare($query);
                    if ($params) {
                        $types = str_repeat('s', count($params));
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                }
                
                $no = 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['tanggal']}</td>
                                <td>{$row['nama_lengkap']}</td>
                                <td>" . substr($row['aktivitas'], 0, 50) . "...</td>
                                <td>{$row['status']}</td>
                                <td class='action-buttons'>
                                    <button class='btn btn-view' onclick='viewReport({$row['id']})'><i class='bi bi-eye'></i> Lihat</button>
                                    <button class='btn btn-edit' onclick='editReport({$row['id']})'><i class='bi bi-pencil'></i> Edit</button>
                                    <button class='btn btn-delete' onclick='deleteReport({$row['id']})'><i class='bi bi-trash'></i> Hapus</button>
                                </td>
                              </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center;'>Tidak ada data laporan kinerja</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Viewing Report -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal('viewModal')">&times;</span>
        <h3 class="modal-title">Detail Laporan Kinerja</h3>
        <div id="modalBody"></div>
    </div>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> Aplikasi Monitoring & Evaluasi Pegawai - Beny Lakamal - 082236953573
</footer>

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

    // Function to view report details
    function viewReport(id) {
        fetch('get_laporan_detail.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const report = data.data;
                    let html = `
                        <div class="detail-row">
                            <div class="detail-label">Pegawai:</div>
                            <div>${report.nama_lengkap}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Tanggal:</div>
                            <div>${report.tanggal}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Aktivitas:</div>
                            <div>${report.aktivitas}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Output:</div>
                            <div>${report.output}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Volume:</div>
                            <div>${report.volume}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Status:</div>
                            <div>${report.status}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Keterangan:</div>
                            <div>${report.keterangan || '-'}</div>
                        </div>
                    `;
                    document.getElementById('modalBody').innerHTML = html;
                    document.getElementById('viewModal').style.display = 'flex';
                } else {
                    alert('Gagal mengambil data laporan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data');
            });
    }

    // Function to edit report
    function editReport(id) {
        window.location.href = 'edit_laporan.php?id=' + id;
    }

    // Function to delete report
    function deleteReport(id) {
        if (confirm('Apakah Anda yakin ingin menghapus laporan ini?')) {
            fetch('delete_laporan.php?id=' + id, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Laporan berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal menghapus laporan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data');
            });
        }
    }

    // Function to close modal
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        const modals = document.getElementsByClassName('modal');
        for (let modal of modals) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    }
</script>

</body>
</html>