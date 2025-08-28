<?php
include "db_config.php";

if (isset($_GET['nip']) && isset($_GET['periode'])) {
    $nip = $conn->real_escape_string($_GET['nip']);
    $periode = $conn->real_escape_string($_GET['periode']); // format 2025-08
    list($tahun, $bulan) = explode("-", $periode);

    // Hitung rata-rata SKP
    $sql_skp = "SELECT AVG(nilai_ekp) as avg_skp 
                FROM skp 
                WHERE nip='$nip' AND bulan='$bulan' AND tahun='$tahun'";
    $result_skp = $conn->query($sql_skp);
    $avg_skp = ($result_skp->num_rows > 0) ? $result_skp->fetch_assoc()['avg_skp'] : 0;

    // Hitung kehadiran (presensi)
    $sql_presensi = "SELECT COUNT(DISTINCT tanggal) as hadir
                     FROM presensi
                     WHERE pegawai_id=(SELECT id FROM pegawai WHERE nip='$nip')
                     AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'";
    $result_presensi = $conn->query($sql_presensi);
    $hadir = ($result_presensi->num_rows > 0) ? $result_presensi->fetch_assoc()['hadir'] : 0;

    // Total hari kerja (anggap 22 hari kerja sebulan)
    $hari_kerja = 22;
    $persen_presensi = ($hadir / $hari_kerja) * 100;

    // Kombinasi skor (70% SKP, 30% Presensi)
    $total = (0.7 * $avg_skp) + (0.3 * $persen_presensi);

    echo json_encode([
        "avg_skp" => round($avg_skp, 2),
        "persen_presensi" => round($persen_presensi, 2),
        "total" => round($total, 2)
    ]);
}
?>
