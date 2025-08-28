<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Berhasil Disimpan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #2c2f33;
            color: #f1f1f1;
        }
        .navbar {
            background-color: #23272a !important;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            background-color: #23272a;
            padding-top: 60px;
        }
        .sidebar a {
            color: #dcdcdc;
            display: block;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #2e59d9;
            color: white;
        }
        .content {
            margin-left: 220px;
            padding: 30px;
        }
        .card {
            background-color: #3a3d42;
            border: none;
            color: #eaeaea;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
            padding: 20px;
            text-align: center;
        }
        .btn-home {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Monev Pegawai</a>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <a href="../dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="../pengajuan.php"><i class="bi bi-file-earmark-text"></i> Pengajuan</a>
    <a href="../laporan.php"><i class="bi bi-bar-chart"></i> Laporan</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Content -->
<div class="content">
    <div class="card">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
        <h2 class="mt-3">Profil Berhasil Disimpan!</h2>
        <p>Data profil Anda telah berhasil diperbarui di sistem.</p>
        <a href="../dashboard.php" class="btn btn-primary btn-home">
            <i class="bi bi-house-door"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

</body>
</html>
