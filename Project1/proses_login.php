<?php
session_start();
include 'db_config.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    // LOGIN TANPA HASH
    if ($password === $user['password']) {
        $_SESSION['user'] = $user;

        // Arahkan berdasarkan role
        if ($user['role'] === 'administrator') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] === 'pegawai') {
            header("Location: dashboard.php");
        } else {
            // Jika role tidak dikenali
            $_SESSION['error'] = "Role tidak dikenali!";
            header("Location: index.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Password salah!";
        header("Location: index.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Username tidak ditemukan!";
    header("Location: index.php");
    exit();
}
?>
