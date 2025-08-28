<?php
session_start();
include("../db_config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID not provided']);
    exit;
}

$id = intval($_GET['id']);
$nip = $_SESSION['user']['nip'];

$query = "SELECT * FROM laporan_kinerja WHERE id = ? AND nip = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $id, $nip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $report = $result->fetch_assoc();
    echo json_encode(['success' => true, 'report' => $report]);
} else {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
}

$conn->close();
?>