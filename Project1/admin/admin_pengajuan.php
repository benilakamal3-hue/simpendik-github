<?php
session_start();
include '../db_config.php';

// Hanya admin yang bisa mengakses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'administrator') {
    header("Location: ../index.php");
    exit();
}

// Ambil semua pengajuan dari database
$query = "SELECT * FROM pengajuan ORDER BY created_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>📄 Data Pengajuan Pegawai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #e4e8ed);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background-attachment: fixed;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        h2 {
            color: var(--secondary);
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-filter-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 40px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 14px;
            background: white;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }

        .filter-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-box select {
            padding: 12px 15px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .filter-box select:focus {
            outline: none;
            border-color: var(--accent);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            position: relative;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
        }

        th {
            background: var(--secondary);
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 500;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            white-space: nowrap;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(26, 188, 156, 0.05);
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-menunggu {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f39c12;
        }

        .status-disetujui {
            background-color: rgba(46, 204, 113, 0.1);
            color: #27ae60;
        }

        .status-ditolak {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .file-link {
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .file-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-back {
            background: var(--secondary);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-back:hover {
            background: #1a252f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(44, 62, 80, 0.3);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        tr {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }

        .pagination button:hover {
            background: #f5f5f5;
        }

        .pagination button.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .search-filter-container {
                flex-direction: column;
            }
            
            .filter-box {
                flex-wrap: wrap;
            }
            
            .btn-back {
                width: 100%;
                justify-content: center;
            }
        }

        /* Loading animation */
        .loading {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 10;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            border-radius: 12px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(52, 152, 219, 0.2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Empty state */
        .empty-state {
            display: none;
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #bdc3c7;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        /* Highlight animation */
        @keyframes highlight {
            0% { background-color: rgba(26, 188, 156, 0.3); }
            100% { background-color: transparent; }
        }

        .highlight {
            animation: highlight 1.5s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-file-alt"></i> Data Pengajuan Pegawai</h2>
            <a href="../admin_dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="search-filter-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Cari nama, NIP, atau jenis pengajuan...">
            </div>
            
            <div class="filter-box">
                <select id="statusFilter">
                    <option value="">Semua Status</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Disetujui">Disetujui</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
                
                <select id="jenisFilter">
                    <option value="">Semua Jenis</option>
                    <option value="Cuti">Cuti</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>Memuat data...</p>
            </div>

            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Tidak ada data pengajuan yang ditemukan</p>
            </div>

            <table id="pengajuanTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Jenis</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Lama (Hari)</th>
                        <th>Keterangan</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['nip']) ?></td>
                            <td><?= htmlspecialchars($row['jabatan']) ?></td>
                            <td><?= htmlspecialchars($row['jenis']) ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></td>
                            <td><?= $row['lama_hari'] ?></td>
                            <td class="tooltip">
                                <?= strlen($row['keterangan']) > 20 ? substr(htmlspecialchars($row['keterangan']), 0, 20) . '...' : htmlspecialchars($row['keterangan']) ?>
                                <?php if(strlen($row['keterangan']) > 20) : ?>
                                    <span class="tooltiptext"><?= htmlspecialchars($row['keterangan']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['nama_file'])) { ?>
                                    <a href="../uploads/<?= urlencode($row['nama_file']) ?>" class="file-link" target="_blank">
                                        <i class="fas fa-file-download"></i> Unduh
                                    </a>
                                <?php } else { echo "-"; } ?>
                            </td>
                            <td>
                                <span class="status status-<?= strtolower($row['status']) ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td class="tooltip">
                                <?= strlen($row['catatan_admin']) > 20 ? substr(htmlspecialchars($row['catatan_admin']), 0, 20) . '...' : htmlspecialchars($row['catatan_admin']) ?>
                                <?php if(strlen($row['catatan_admin']) > 20) : ?>
                                    <span class="tooltiptext"><?= htmlspecialchars($row['catatan_admin']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary" onclick="showEditModal(<?= $row['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pagination">
            <button id="prevBtn"><i class="fas fa-chevron-left"></i></button>
            <div id="pageNumbers"></div>
            <button id="nextBtn"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3><i class="fas fa-edit"></i> Edit Status Pengajuan</h3>
            <form id="editForm">
                <input type="hidden" id="editId">
                <div class="form-group">
                    <label for="editStatus">Status</label>
                    <select id="editStatus" class="form-control">
                        <option value="Menunggu">Menunggu</option>
                        <option value="Disetujui">Disetujui</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editCatatan">Catatan Admin</label>
                    <textarea id="editCatatan" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }

        .close:hover {
            color: var(--danger);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(26, 188, 156, 0.2);
        }
    </style>

    <script>
        // Enhanced search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const jenisFilter = document.getElementById('jenisFilter');
            const table = document.getElementById('pengajuanTable');
            const rows = table.querySelectorAll('tbody tr');
            const loading = document.querySelector('.loading');
            const emptyState = document.querySelector('.empty-state');
            const tbody = table.querySelector('tbody');
            
            // Pagination variables
            const rowsPerPage = 10;
            let currentPage = 1;
            let filteredRows = Array.from(rows);
            
            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                const jenisValue = jenisFilter.value;
                
                loading.style.display = 'flex';
                tbody.style.display = 'none';
                
                setTimeout(() => {
                    let visibleCount = 0;
                    
                    filteredRows = Array.from(rows).filter(row => {
                        const nama = row.cells[1].textContent.toLowerCase();
                        const nip = row.cells[2].textContent.toLowerCase();
                        const jenis = row.cells[4].textContent;
                        const status = row.querySelector('.status').textContent;
                        
                        const matchesSearch = searchTerm === '' || 
                            nama.includes(searchTerm) || 
                            nip.includes(searchTerm) || 
                            jenis.toLowerCase().includes(searchTerm);
                        
                        const matchesStatus = statusValue === '' || status === statusValue;
                        const matchesJenis = jenisValue === '' || jenis === jenisValue;
                        
                        if (matchesSearch && matchesStatus && matchesJenis) {
                            visibleCount++;
                            row.style.display = '';
                            return true;
                        } else {
                            row.style.display = 'none';
                            return false;
                        }
                    });
                    
                    if (visibleCount === 0) {
                        emptyState.style.display = 'block';
                    } else {
                        emptyState.style.display = 'none';
                    }
                    
                    tbody.style.display = '';
                    loading.style.display = 'none';
                    updatePagination();
                    showPage(1);
                }, 300);
            }
            
            // Pagination functions
            function updatePagination() {
                const pageCount = Math.ceil(filteredRows.length / rowsPerPage);
                const pageNumbers = document.getElementById('pageNumbers');
                pageNumbers.innerHTML = '';
                
                for (let i = 1; i <= pageCount; i++) {
                    const button = document.createElement('button');
                    button.textContent = i;
                    if (i === currentPage) {
                        button.classList.add('active');
                    }
                    button.addEventListener('click', () => {
                        currentPage = i;
                        showPage(currentPage);
                        updatePagination();
                    });
                    pageNumbers.appendChild(button);
                }
                
                document.getElementById('prevBtn').disabled = currentPage === 1;
                document.getElementById('nextBtn').disabled = currentPage === pageCount;
            }
            
            function showPage(page) {
                const start = (page - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                
                filteredRows.forEach((row, index) => {
                    if (index >= start && index < end) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            // Event listeners
            searchInput.addEventListener('input', filterRows);
            statusFilter.addEventListener('change', filterRows);
            jenisFilter.addEventListener('change', filterRows);
            
            document.getElementById('prevBtn').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    showPage(currentPage);
                    updatePagination();
                }
            });
            
            document.getElementById('nextBtn').addEventListener('click', () => {
                if (currentPage < Math.ceil(filteredRows.length / rowsPerPage)) {
                    currentPage++;
                    showPage(currentPage);
                    updatePagination();
                }
            });
            
            // Initialize
            filterRows();
            
            // Highlight row on hover
            table.addEventListener('mouseover', function(e) {
                if (e.target.tagName === 'TD') {
                    const row = e.target.parentElement;
                    row.style.transform = 'scale(1.01)';
                    row.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)';
                }
            });
            
            table.addEventListener('mouseout', function(e) {
                if (e.target.tagName === 'TD') {
                    const row = e.target.parentElement;
                    row.style.transform = '';
                    row.style.boxShadow = '';
                }
            });
        });
        
        // Modal functionality
        const modal = document.getElementById('editModal');
        const closeBtn = document.querySelector('.close');
        
        function showEditModal(id) {
            // In a real application, you would fetch the data for this ID
            document.getElementById('editId').value = id;
            
            // For demo purposes, we'll just show the modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        });
        
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
        
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // In a real application, you would send this data to the server
            const id = document.getElementById('editId').value;
            const status = document.getElementById('editStatus').value;
            const catatan = document.getElementById('editCatatan').value;
            
            // Simulate AJAX call
            setTimeout(() => {
                alert(`Status pengajuan ID ${id} berhasil diperbarui!`);
                modal.style.display = 'none';
                document.body.style.overflow = '';
                
                // Highlight the updated row
                const rows = document.querySelectorAll('#pengajuanTable tbody tr');
                rows.forEach(row => {
                    if (row.cells[0].textContent == id) {
                        row.querySelector('.status').textContent = status;
                        row.querySelector('.status').className = `status status-${status.toLowerCase()}`;
                        row.cells[11].textContent = catatan.length > 20 ? catatan.substring(0, 20) + '...' : catatan;
                        
                        // Add highlight animation
                        row.classList.add('highlight');
                        setTimeout(() => {
                            row.classList.remove('highlight');
                        }, 1500);
                    }
                });
            }, 1000);
        });
        
        // Export to Excel functionality
        function exportToExcel() {
            // In a real application, you would implement this
            alert('Fitur ekspor ke Excel akan diimplementasikan di sini!');
        }
    </script>
</body>
</html>