<?php
// simpan_skp.php
session_start();
require_once '../db_config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user']) && !isset($_SESSION['nip'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil NIP dari form atau session
$nip = $_POST['nip'] ?? ($_SESSION['nip'] ?? '');
$bulan = $_POST['bulan'] ?? '';
$uraian = $_POST['uraian'] ?? '';
$output = $_POST['output'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';

// Validasi input sederhana
if (empty($nip) || empty($bulan) || empty($uraian) || empty($output)) {
    echo "<script>alert('Semua field wajib diisi!'); window.history.back();</script>";
    exit();
}

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO skp (nip, bulan, uraian, output, keterangan, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sisss", $nip, $bulan, $uraian, $output, $keterangan);

if ($stmt->execute()) {
    $stmt->close();
    echo "<script>alert('SKP berhasil disimpan'); window.location.href='skp.php';</script>";
    exit();
} else {
    $error = $stmt->error;
    $stmt->close();
    echo "<script>alert('Terjadi kesalahan: " . addslashes($error) . "'); window.history.back();</script>";
    exit();
}
?>
