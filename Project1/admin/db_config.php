<?php
$host = "localhost:3306";
$user = "root";
$pass = "123456"; 
$db   = "monev_db"; 

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
