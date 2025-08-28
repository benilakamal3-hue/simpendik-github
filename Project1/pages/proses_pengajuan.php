<?php
// proses_pengajuan.php
session_start();
require_once '../db_config.php';

// Helper: hitung selisih hari inklusif
function days_between($d1, $d2) {
    $dt1 = new DateTime($d1);
    $dt2 = new DateTime($d2);
    return $dt1->diff($dt2)->days + 1;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & validasi
    $nama       = trim($_POST['nama_lengkap'] ?? '');
    $nip        = trim($_POST['nip'] ?? '');
    $jabatan    = trim($_POST['jabatan'] ?? '');
    $jenis      = $_POST['jenis'] ?? '';
    $tanggal_mulai   = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Validasi dasar
    $errors = [];
    if ($nama === '') $errors[] = "Nama wajib diisi.";
    if ($nip === '') $errors[] = "NIP wajib diisi.";
    if ($jabatan === '') $errors[] = "Jabatan wajib diisi.";
    if (!in_array($jenis, ['Cuti','Izin','Tugas Luar','Lainnya'])) $errors[] = "Jenis pengajuan tidak valid.";
    if (!$tanggal_mulai || !$tanggal_selesai) $errors[] = "Tanggal mulai dan selesai wajib diisi.";
    if ($tanggal_mulai > $tanggal_selesai) $errors[] = "Tanggal mulai tidak boleh setelah tanggal selesai.";

    if (!empty($errors)) {
        $msg = implode("\\n", $errors);
        echo "<script>alert('Gagal: \\n{$msg}'); window.history.back();</script>";
        exit;
    }

    $lama = days_between($tanggal_mulai, $tanggal_selesai);

    // Handle file upload (opsional)
    $uploadedFileName = null;
    if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['lampiran'];

        $maxSize = 5 * 1024 * 1024; // 5 MB
        $allowed = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        ];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo "<script>alert('Gagal upload file. Error code: {$file['error']}'); window.history.back();</script>";
            exit;
        }
        if ($file['size'] > $maxSize) {
            echo "<script>alert('Ukuran file melebihi 5MB.'); window.history.back();</script>";
            exit;
        }

        // Cek tipe file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            echo "<script>alert('Tipe file tidak diperbolehkan. Hanya pdf/doc/docx/jpg/png.'); window.history.back();</script>";
            exit;
        }

        // Simpan file dengan nama unik
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $namaBaru = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetDir = __DIR__ . '/uploads/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        $targetPath = $targetDir . $namaBaru;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo "<script>alert('Gagal menyimpan file.'); window.history.back();</script>";
            exit;
        }
        $uploadedFileName = $namaBaru;
    }

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO pengajuan 
        (nama_lengkap, nip, jabatan, jenis, tanggal_mulai, tanggal_selesai, lama_hari, keterangan, nama_file, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu')");
    $stmt->bind_param('ssssssiss', 
        $nama, $nip, $jabatan, $jenis, $tanggal_mulai, $tanggal_selesai, $lama, $keterangan, $uploadedFileName
    );

    if ($stmt->execute()) {
        echo "<script>alert('Pengajuan berhasil dikirim.'); window.location='form.php';</script>";
    } else {
        if ($uploadedFileName) {
            @unlink(__DIR__ . '/uploads/' . $uploadedFileName);
        }
        echo "<script>alert('Gagal menyimpan pengajuan: {$stmt->error}'); window.history.back();</script>";
    }
    exit;
}

header("Location: form.php");
exit;
