<?php
session_start();
include("../db_config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username     = $_POST['username'];
    $nama         = $_POST['nama_lengkap'];
    $nip          = $_POST['nip'];
    $jabatan      = $_POST['jabatan'];
    $unit_kerja   = $_POST['unit_kerja'];
    $email        = $_POST['email'];
    $telepon      = $_POST['telepon'];

    // Update data
    $query = "UPDATE pegawai SET 
                nama_lengkap = '$nama',
                nip = '$nip',
                jabatan = '$jabatan',
                unit_kerja = '$unit_kerja',
                email = '$email',
                telepon = '$telepon'
              WHERE username = '$username'";

    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='profil.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
