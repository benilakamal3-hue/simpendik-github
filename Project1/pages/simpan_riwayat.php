<?php
session_start();
require_once '../db_config.php';

// Ambil data inputan
$nama_lengkap      = $_POST['nama_lengkap'];
$agama             = $_POST['agama'];
$status_perkawinan = $_POST['status_perkawinan'];
$jenis_kelamin     = $_POST['jenis_kelamin'];
$alamat            = $_POST['alamat'];
$tempat_lahir      = $_POST['tempat_lahir'];
$tanggal_lahir     = $_POST['tanggal_lahir'];
$suku              = $_POST['suku'];
$tinggi_badan      = $_POST['tinggi_badan'];
$berat_badan       = $_POST['berat_badan'];
$golongan_darah    = $_POST['golongan_darah'];
$catatan           = $_POST['catatan'];

// Query simpan
$sql = "INSERT INTO riwayat_hidup 
(nama_lengkap, agama, status_perkawinan, jenis_kelamin, alamat, tempat_lahir, tanggal_lahir, suku, tinggi_badan, berat_badan, golongan_darah, catatan)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssii ss", 
    $nama_lengkap, $agama, $status_perkawinan, $jenis_kelamin, 
    $alamat, $tempat_lahir, $tanggal_lahir, 
    $suku, $tinggi_badan, $berat_badan, 
    $golongan_darah, $catatan
);

if ($stmt->execute()) {
    echo "✅ Data berhasil disimpan. <a href='riwayat_hidup.php'>Kembali</a>";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
