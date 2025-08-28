<?php
session_start();
require_once "../db_config.php"; // koneksi MySQL

$success = "";
$error = "";
$showPrint = false;
$lastInsertId = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil semua data dari form
    $data = $_POST;

    $sql = "INSERT INTO riwayat_hidup (
        nama_lengkap, agama, status_perkawinan, jenis_kelamin, alamat, tempat_lahir, tanggal_lahir, suku,
        tinggi_badan, berat_badan, rambut, mata, ciri_lain, golongan_darah,
        pendidikan_sd, pendidikan_smp, pendidikan_sma, pendidikan_pt, kursus, prestasi,
        pengalaman_kerja,
        nama_ayah_kandung, ttl_ayah_kandung, pekerjaan_ayah_kandung, alamat_ayah_kandung,
        nama_ibu_kandung, ttl_ibu_kandung, pekerjaan_ibu_kandung, alamat_ibu_kandung,
        nama_ayah_wali, ttl_ayah_wali, pekerjaan_ayah_wali, alamat_ayah_wali,
        nama_ibu_wali, ttl_ibu_wali, pekerjaan_ibu_wali, alamat_ibu_wali,
        saudara_kandung, saudara_bapak, saudara_ibu,
        organisasi, olahraga, kesenian,
        tidak_pernah_dipidana, terikat_instansi_lain, catatan
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Checkbox default 0
    $tidak_pernah_dipidana = isset($data['tidak_pernah_dipidana']) ? 1 : 0;
    $terikat_instansi_lain = isset($data['terikat_instansi_lain']) ? 1 : 0;

    $stmt->bind_param(
        "sssssssiiissssssssssssssssssssssssssssssssssss",
        $data['nama_lengkap'], $data['agama'], $data['status_perkawinan'], $data['jenis_kelamin'],
        $data['alamat'], $data['tempat_lahir'], $data['tanggal_lahir'], $data['suku'],
        $data['tinggi_badan'], $data['berat_badan'], $data['rambut'], $data['mata'], $data['ciri_lain'], $data['golongan_darah'],
        $data['pendidikan_sd'], $data['pendidikan_smp'], $data['pendidikan_sma'], $data['pendidikan_pt'], $data['kursus'], $data['prestasi'],
        $data['pengalaman_kerja'],
        $data['nama_ayah_kandung'], $data['ttl_ayah_kandung'], $data['pekerjaan_ayah_kandung'], $data['alamat_ayah_kandung'],
        $data['nama_ibu_kandung'], $data['ttl_ibu_kandung'], $data['pekerjaan_ibu_kandung'], $data['alamat_ibu_kandung'],
        $data['nama_ayah_wali'], $data['ttl_ayah_wali'], $data['pekerjaan_ayah_wali'], $data['alamat_ayah_wali'],
        $data['nama_ibu_wali'], $data['ttl_ibu_wali'], $data['pekerjaan_ibu_wali'], $data['alamat_ibu_wali'],
        $data['saudara_kandung'], $data['saudara_bapak'], $data['saudara_ibu'],
        $data['organisasi'], $data['olahraga'], $data['kesenian'],
        $tidak_pernah_dipidana, $terikat_instansi_lain, $data['catatan']
    );

    if ($stmt->execute()) {
        $success = "Data berhasil disimpan!";
        $lastInsertId = $stmt->insert_id;
        $showPrint = true;
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Fungsi untuk mengambil data berdasarkan ID
function getRiwayatHidup($conn, $id) {
    $sql = "SELECT * FROM riwayat_hidup WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Jika ada request untuk mencetak
if (isset($_GET['print']) && isset($_GET['id'])) {
    $printId = $_GET['id'];
    $printData = getRiwayatHidup($conn, $printId);
    
    if ($printData) {
        // Generate halaman cetak
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Cetak Riwayat Hidup - <?php echo htmlspecialchars($printData['nama_lengkap']); ?></title>
            <style>
                @media print {
                    body { margin: 0; }
                    .no-print { display: none !important; }
                    .page-break { page-break-before: always; }
                }
                
                body {
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    line-height: 1.4;
                    margin: 20px;
                    color: #000;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #000;
                    padding-bottom: 15px;
                }
                
                .header h1 {
                    font-size: 18px;
                    margin: 0 0 5px 0;
                    text-transform: uppercase;
                    font-weight: bold;
                }
                
                .header h2 {
                    font-size: 16px;
                    margin: 0;
                    font-weight: normal;
                }
                
                .section {
                    margin-bottom: 25px;
                }
                
                .section-title {
                    background-color: #f0f0f0;
                    padding: 8px 12px;
                    font-weight: bold;
                    text-transform: uppercase;
                    border: 1px solid #ccc;
                    margin-bottom: 10px;
                }
                
                .data-row {
                    display: flex;
                    margin-bottom: 5px;
                }
                
                .data-label {
                    width: 200px;
                    font-weight: bold;
                    flex-shrink: 0;
                }
                
                .data-value {
                    flex: 1;
                    border-bottom: 1px dotted #ccc;
                    min-height: 15px;
                    padding-left: 10px;
                }
                
                .data-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 15px;
                }
                
                .data-full {
                    grid-column: 1 / -1;
                }
                
                .checkbox-item {
                    display: flex;
                    align-items: center;
                    margin-bottom: 5px;
                }
                
                .checkbox {
                    width: 15px;
                    height: 15px;
                    border: 1px solid #000;
                    margin-right: 10px;
                    display: inline-block;
                    text-align: center;
                    font-weight: bold;
                }
                
                .checked {
                    background-color: #000;
                    color: white;
                }
                
                .signature-section {
                    margin-top: 40px;
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 50px;
                }
                
                .signature-box {
                    text-align: center;
                }
                
                .signature-line {
                    border-bottom: 1px solid #000;
                    height: 60px;
                    margin-bottom: 5px;
                }
                
                .print-btn {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #007bff;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                    z-index: 1000;
                }
                
                .print-btn:hover {
                    background: #0056b3;
                }
                
                .back-btn {
                    position: fixed;
                    top: 20px;
                    left: 20px;
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                    z-index: 1000;
                    text-decoration: none;
                    display: inline-block;
                }
                
                .back-btn:hover {
                    background: #545b62;
                    color: white;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <button class="print-btn no-print" onclick="window.print()">🖨️ Cetak</button>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="back-btn no-print">← Kembali</a>
            
            <div class="header">
                <h1>DAFTAR RIWAYAT HIDUP</h1>
                <h2>(CURRICULUM VITAE)</h2>
            </div>
            
            <!-- Data Diri -->
            <div class="section">
                <div class="section-title">A. DATA DIRI</div>
                <div class="data-grid">
                    <div class="data-row">
                        <div class="data-label">Nama Lengkap</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['nama_lengkap'] ?? ''); ?></div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Jenis Kelamin</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['jenis_kelamin'] ?? ''); ?></div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Agama</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['agama'] ?? ''); ?></div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Status Perkawinan</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['status_perkawinan'] ?? ''); ?></div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Tempat/Tanggal Lahir</div>
                        <div class="data-value">
                            <?php 
                            $ttl = htmlspecialchars($printData['tempat_lahir'] ?? '');
                            if (!empty($printData['tanggal_lahir'])) {
                                $ttl .= ', ' . date('d-m-Y', strtotime($printData['tanggal_lahir']));
                            }
                            echo $ttl;
                            ?>
                        </div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Suku</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['suku'] ?? ''); ?></div>
                    </div>
                </div>
                
                <div class="data-row data-full">
                    <div class="data-label">Alamat</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['alamat'] ?? ''); ?></div>
                </div>
                
                <div class="data-grid">
                    <div class="data-row">
                        <div class="data-label">Tinggi Badan</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['tinggi_badan'] ?? ''); ?> cm</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Berat Badan</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['berat_badan'] ?? ''); ?> kg</div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Golongan Darah</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['golongan_darah'] ?? ''); ?></div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Rambut</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['rambut'] ?? ''); ?></div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Mata</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['mata'] ?? ''); ?></div>
                    </div>
                    <div class="data-row">
                        <div class="data-label">Ciri Lainnya</div>
                        <div class="data-value"><?php echo htmlspecialchars($printData['ciri_lain'] ?? ''); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Pendidikan -->
            <div class="section">
                <div class="section-title">B. PENDIDIKAN</div>
                <div class="data-row">
                    <div class="data-label">Sekolah Dasar</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pendidikan_sd'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Sekolah Menengah Pertama</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pendidikan_smp'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Sekolah Menengah Atas</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pendidikan_sma'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Perguruan Tinggi</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pendidikan_pt'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Kursus/Pelatihan</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['kursus'] ?? '')); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Prestasi</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['prestasi'] ?? '')); ?></div>
                </div>
            </div>
            
            <!-- Pengalaman Kerja -->
            <div class="section">
                <div class="section-title">C. PENGALAMAN KERJA</div>
                <div class="data-row">
                    <div class="data-label">Pengalaman Kerja</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['pengalaman_kerja'] ?? '')); ?></div>
                </div>
            </div>
            
            <!-- Data Orang Tua -->
            <div class="section page-break">
                <div class="section-title">D. DATA ORANG TUA KANDUNG</div>
                
                <h4 style="margin: 15px 0 10px 0;">AYAH KANDUNG</h4>
                <div class="data-row">
                    <div class="data-label">Nama</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['nama_ayah_kandung'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Tempat/Tanggal Lahir</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['ttl_ayah_kandung'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Pekerjaan</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pekerjaan_ayah_kandung'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Alamat</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['alamat_ayah_kandung'] ?? ''); ?></div>
                </div>
                
                <h4 style="margin: 15px 0 10px 0;">IBU KANDUNG</h4>
                <div class="data-row">
                    <div class="data-label">Nama</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['nama_ibu_kandung'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Tempat/Tanggal Lahir</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['ttl_ibu_kandung'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Pekerjaan</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pekerjaan_ibu_kandung'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Alamat</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['alamat_ibu_kandung'] ?? ''); ?></div>
                </div>
            </div>
            
            <!-- Data Wali -->
            <?php if (!empty($printData['nama_ayah_wali']) || !empty($printData['nama_ibu_wali'])): ?>
            <div class="section">
                <div class="section-title">E. DATA WALI</div>
                
                <?php if (!empty($printData['nama_ayah_wali'])): ?>
                <h4 style="margin: 15px 0 10px 0;">AYAH WALI</h4>
                <div class="data-row">
                    <div class="data-label">Nama</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['nama_ayah_wali'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Tempat/Tanggal Lahir</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['ttl_ayah_wali'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Pekerjaan</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pekerjaan_ayah_wali'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Alamat</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['alamat_ayah_wali'] ?? ''); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($printData['nama_ibu_wali'])): ?>
                <h4 style="margin: 15px 0 10px 0;">IBU WALI</h4>
                <div class="data-row">
                    <div class="data-label">Nama</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['nama_ibu_wali'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Tempat/Tanggal Lahir</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['ttl_ibu_wali'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Pekerjaan</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['pekerjaan_ibu_wali'] ?? ''); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Alamat</div>
                    <div class="data-value"><?php echo htmlspecialchars($printData['alamat_ibu_wali'] ?? ''); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Data Saudara -->
            <div class="section">
                <div class="section-title">F. DATA SAUDARA</div>
                <div class="data-row">
                    <div class="data-label">Saudara Kandung</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['saudara_kandung'] ?? '')); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Saudara dari Bapak</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['saudara_bapak'] ?? '')); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Saudara dari Ibu</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['saudara_ibu'] ?? '')); ?></div>
                </div>
            </div>
            
            <!-- Kegiatan -->
            <div class="section">
                <div class="section-title">G. KEGIATAN</div>
                <div class="data-row">
                    <div class="data-label">Organisasi</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['organisasi'] ?? '')); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Olahraga</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['olahraga'] ?? '')); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Kesenian</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['kesenian'] ?? '')); ?></div>
                </div>
            </div>
            
            <!-- Lainnya -->
            <div class="section">
                <div class="section-title">H. LAINNYA</div>
                <div class="checkbox-item">
                    <span class="checkbox <?php echo $printData['tidak_pernah_dipidana'] ? 'checked' : ''; ?>">
                        <?php echo $printData['tidak_pernah_dipidana'] ? '✓' : ''; ?>
                    </span>
                    Saya tidak pernah dipidana karena melakukan suatu kejahatan
                </div>
                <div class="checkbox-item">
                    <span class="checkbox <?php echo $printData['terikat_instansi_lain'] ? 'checked' : ''; ?>">
                        <?php echo $printData['terikat_instansi_lain'] ? '✓' : ''; ?>
                    </span>
                    Saya sedang terikat dengan instansi lain
                </div>
                
                <?php if (!empty($printData['catatan'])): ?>
                <div class="data-row" style="margin-top: 15px;">
                    <div class="data-label">Catatan Lainnya</div>
                    <div class="data-value"><?php echo nl2br(htmlspecialchars($printData['catatan'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Tanda Tangan -->
            <div class="signature-section">
                <div class="signature-box">
                    <div>Hormat saya,</div>
                    <div class="signature-line"></div>
                    <div><?php echo htmlspecialchars($printData['nama_lengkap']); ?></div>
                </div>
                <div class="signature-box">
                    <div>Kupang, <?php echo date('d F Y'); ?></div>
                    <div class="signature-line"></div>
                    <div>( Tanda Tangan )</div>
                </div>
            </div>
            
            <script>
                // Auto print jika diperlukan
                function autoPrint() {
                    if (confirm('Apakah Anda ingin langsung mencetak dokumen ini?')) {
                        window.print();
                    }
                }
                
                // Panggil auto print setelah halaman dimuat
                window.onload = function() {
                    setTimeout(autoPrint, 500);
                };
            </script>
        </body>
        </html>
        <?php
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Riwayat Hidup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #27ae60;
            --light-bg: #f5f7fb;
            --card-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #e4eaf1 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            padding: 20px;
            min-height: 100vh;
        }
        
        .form-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 30px;
            box-shadow: var(--card-shadow);
            border-radius: 15px;
            background-color: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary);
        }
        
        .header h1 {
            color: var(--secondary);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .section-title {
            background: linear-gradient(to right, #3498db, #2c3e50);
            color: white;
            padding: 12px 20px;
            margin-top: 25px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .required:after {
            content: " *";
            color: #e74c3c;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(to right, #3498db, #2980b9);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: linear-gradient(to right, #7f8c8d, #95a5a6);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(to right, #95a5a6, #7f8c8d);
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: linear-gradient(to right, #27ae60, #2ecc71);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
            color: white;
        }
        
        .btn-success:hover {
            background: linear-gradient(to right, #2ecc71, #27ae60);
            transform: translateY(-2px);
            color: white;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #28a745;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 5px solid #dc3545;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s;
        }
        
        .success-message i, .error-message i {
            font-size: 2rem;
            margin-right: 15px;
        }
        
        .success-message i {
            color: #28a745;
        }
        
        .error-message i {
            color: #dc3545;
        }
        
        .print-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
            animation: slideInDown 0.6s;
        }
        
        .print-success h3 {
            color: #155724;
            margin-bottom: 15px;
        }
        
        .print-success p {
            color: #155724;
            margin-bottom: 20px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .info-card {
            background-color: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid var(--primary);
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            margin-top: 5px;
        }
        
        .btn-group-custom {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            
            .btn-primary, .btn-secondary, .btn-success {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .btn-group-custom {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="header">
            <h1><i class="fas fa-user-circle"></i> Form Riwayat Hidup</h1>
            <p>Isi data diri Anda dengan lengkap dan benar</p>
        </div>
        
        <?php if (!empty($success)): ?>
            <div class="print-success">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745; margin-bottom: 15px;"></i>
                <h3>Data Berhasil Disimpan!</h3>
                <p><?php echo $success; ?></p>
                <div class="btn-group-custom">
                    <a href="?print=1&id=<?php echo $lastInsertId; ?>" class="btn btn-success btn-lg" target="_blank">
                        <i class="fas fa-print"></i> Cetak Riwayat Hidup
                    </a>
                    <button type="button" class="btn btn-primary btn-lg" onclick="location.reload()">
                        <i class="fas fa-plus"></i> Input Data Baru
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <h4>Error!</h4>
                    <p class="mb-0"><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="info-card">
            <h5><i class="fas fa-info-circle"></i> Informasi</h5>
            <p class="mb-0">Form ini untuk mengisi data riwayat hidup. Field yang ditandai dengan asterisk (<span class="required"></span>) wajib diisi. Setelah data tersimpan, Anda dapat langsung mencetak dokumen riwayat hidup.</p>
        </div>
        
        <form id="riwayatHidupForm" method="POST" action="">
            <!-- Data Diri -->
            <div class="section-title">
                <i class="fas fa-user"></i>
                <span>Data Diri</span>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nama_lengkap" class="form-label required">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    <div class="invalid-feedback">Nama lengkap harus diisi</div>
                </div>
                <div class="col-md-3">
                    <label for="agama" class="form-label required">Agama</label>
                    <select class="form-select" id="agama" name="agama" required>
                        <option value="">Pilih Agama</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Khatolik">Khatolik</option>
                        <option value="Islam">Islam</option>
                        <option value="Budha">Budha</option>
                        <option value="Konghuchu">Konghuchu</option>
                    </select>
                    <div class="invalid-feedback">Agama harus dipilih</div>
                </div>
                <div class="col-md-3">
                    <label for="status_perkawinan" class="form-label required">Status Perkawinan</label>
                    <select class="form-select" id="status_perkawinan" name="status_perkawinan" required>
                        <option value="">Pilih Status</option>
                        <option value="Nikah">Nikah</option>
                        <option value="Belum Nikah">Belum Nikah</option>
                    </select>
                    <div class="invalid-feedback">Status perkawinan harus dipilih</div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="jenis_kelamin" class="form-label required">Jenis Kelamin</label>
                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                    <div class="invalid-feedback">Jenis kelamin harus dipilih</div>
                </div>
                <div class="col-md-3">
                    <label for="tempat_lahir" class="form-label required">Tempat Lahir</label>
                    <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                    <div class="invalid-feedback">Tempat lahir harus diisi</div>
                </div>
                <div class="col-md-3">
                    <label for="tanggal_lahir" class="form-label required">Tanggal Lahir</label>
                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                    <div class="invalid-feedback">Tanggal lahir harus diisi</div>
                </div>
                <div class="col-md-3">
                    <label for="suku" class="form-label">Suku</label>
                    <input type="text" class="form-control" id="suku" name="suku">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="alamat" class="form-label required">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                    <div class="invalid-feedback">Alamat harus diisi</div>
                </div>
                <div class="col-md-2">
                    <label for="tinggi_badan" class="form-label">Tinggi Badan (cm)</label>
                    <input type="number" class="form-control" id="tinggi_badan" name="tinggi_badan" min="0">
                </div>
                <div class="col-md-2">
                    <label for="berat_badan" class="form-label">Berat Badan (kg)</label>
                    <input type="number" class="form-control" id="berat_badan" name="berat_badan" min="0">
                </div>
                <div class="col-md-2">
                    <label for="golongan_darah" class="form-label">Golongan Darah</label>
                    <select class="form-select" id="golongan_darah" name="golongan_darah">
                        <option value="">Pilih</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="AB">AB</option>
                        <option value="O">O</option>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="rambut" class="form-label">Rambut</label>
                    <input type="text" class="form-control" id="rambut" name="rambut" placeholder="Warna, jenis, dll">
                </div>
                <div class="col-md-3">
                    <label for="mata" class="form-label">Mata</label>
                    <input type="text" class="form-control" id="mata" name="mata" placeholder="Warna, bentuk, dll">
                </div>
                <div class="col-md-6">
                    <label for="ciri_lain" class="form-label">Ciri Lainnya</label>
                    <input type="text" class="form-control" id="ciri_lain" name="ciri_lain" placeholder="Ciri fisik khusus">
                </div>
            </div>
            
            <!-- Pendidikan -->
            <div class="section-title">
                <i class="fas fa-graduation-cap"></i>
                <span>Pendidikan</span>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="pendidikan_sd" class="form-label">Pendidikan SD</label>
                    <input type="text" class="form-control" id="pendidikan_sd" name="pendidikan_sd" placeholder="Nama sekolah, tahun lulus">
                </div>
                <div class="col-md-6">
                    <label for="pendidikan_smp" class="form-label">Pendidikan SMP</label>
                    <input type="text" class="form-control" id="pendidikan_smp" name="pendidikan_smp" placeholder="Nama sekolah, tahun lulus">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="pendidikan_sma" class="form-label">Pendidikan SMA/SMK</label>
                    <input type="text" class="form-control" id="pendidikan_sma" name="pendidikan_sma" placeholder="Nama sekolah, tahun lulus, jurusan">
                </div>
                <div class="col-md-6">
                    <label for="pendidikan_pt" class="form-label">Pendidikan Perguruan Tinggi</label>
                    <input type="text" class="form-control" id="pendidikan_pt" name="pendidikan_pt" placeholder="Nama universitas, tahun lulus, jurusan, gelar">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="kursus" class="form-label">Kursus/Pelatihan</label>
                    <textarea class="form-control" id="kursus" name="kursus" rows="3" placeholder="Nama kursus, institusi, tahun"></textarea>
                </div>
                <div class="col-md-6">
                    <label for="prestasi" class="form-label">Prestasi</label>
                    <textarea class="form-control" id="prestasi" name="prestasi" rows="3" placeholder="Penghargaan, prestasi akademik/non-akademik"></textarea>
                </div>
            </div>
            
            <!-- Pengalaman Kerja -->
            <div class="section-title">
                <i class="fas fa-briefcase"></i>
                <span>Pengalaman Kerja</span>
            </div>
            <div class="row mb-3">
                <div class="col-12">
                    <label for="pengalaman_kerja" class="form-label">Pengalaman Kerja</label>
                    <textarea class="form-control" id="pengalaman_kerja" name="pengalaman_kerja" rows="4" placeholder="Nama perusahaan, jabatan, periode kerja, tanggung jawab"></textarea>
                </div>
            </div>
            
            <!-- Data Orang Tua Kandung -->
            <div class="section-title">
                <i class="fas fa-users"></i>
                <span>Data Orang Tua Kandung</span>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5><i class="fas fa-male"></i> Ayah Kandung</h5>
                    <div class="row">
                        <div class="col-12">
                            <label for="nama_ayah_kandung" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama_ayah_kandung" name="nama_ayah_kandung">
                        </div>
                        <div class="col-md-6">
                            <label for="ttl_ayah_kandung" class="form-label">Tempat/Tanggal Lahir</label>
                            <input type="text" class="form-control" id="ttl_ayah_kandung" name="ttl_ayah_kandung" placeholder="Tempat, tanggal-bulan-tahun">
                        </div>
                        <div class="col-md-6">
                            <label for="pekerjaan_ayah_kandung" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="pekerjaan_ayah_kandung" name="pekerjaan_ayah_kandung">
                        </div>
                        <div class="col-12">
                            <label for="alamat_ayah_kandung" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat_ayah_kandung" name="alamat_ayah_kandung" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5><i class="fas fa-female"></i> Ibu Kandung</h5>
                    <div class="row">
                        <div class="col-12">
                            <label for="nama_ibu_kandung" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama_ibu_kandung" name="nama_ibu_kandung">
                        </div>
                        <div class="col-md-6">
                            <label for="ttl_ibu_kandung" class="form-label">Tempat/Tanggal Lahir</label>
                            <input type="text" class="form-control" id="ttl_ibu_kandung" name="ttl_ibu_kandung" placeholder="Tempat, tanggal-bulan-tahun">
                        </div>
                        <div class="col-md-6">
                            <label for="pekerjaan_ibu_kandung" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="pekerjaan_ibu_kandung" name="pekerjaan_ibu_kandung">
                        </div>
                        <div class="col-12">
                            <label for="alamat_ibu_kandung" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat_ibu_kandung" name="alamat_ibu_kandung" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Wali (jika ada) -->
            <div class="section-title">
                <i class="fas fa-hands-helping"></i>
                <span>Data Wali (jika berbeda dengan orang tua kandung)</span>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5><i class="fas fa-male"></i> Ayah Wali</h5>
                    <div class="row">
                        <div class="col-12">
                            <label for="nama_ayah_wali" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama_ayah_wali" name="nama_ayah_wali">
                        </div>
                        <div class="col-md-6">
                            <label for="ttl_ayah_wali" class="form-label">Tempat/Tanggal Lahir</label>
                            <input type="text" class="form-control" id="ttl_ayah_wali" name="ttl_ayah_wali" placeholder="Tempat, tanggal-bulan-tahun">
                        </div>
                        <div class="col-md-6">
                            <label for="pekerjaan_ayah_wali" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="pekerjaan_ayah_wali" name="pekerjaan_ayah_wali">
                        </div>
                        <div class="col-12">
                            <label for="alamat_ayah_wali" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat_ayah_wali" name="alamat_ayah_wali" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5><i class="fas fa-female"></i> Ibu Wali</h5>
                    <div class="row">
                        <div class="col-12">
                            <label for="nama_ibu_wali" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama_ibu_wali" name="nama_ibu_wali">
                        </div>
                        <div class="col-md-6">
                            <label for="ttl_ibu_wali" class="form-label">Tempat/Tanggal Lahir</label>
                            <input type="text" class="form-control" id="ttl_ibu_wali" name="ttl_ibu_wali" placeholder="Tempat, tanggal-bulan-tahun">
                        </div>
                        <div class="col-md-6">
                            <label for="pekerjaan_ibu_wali" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control" id="pekerjaan_ibu_wali" name="pekerjaan_ibu_wali">
                        </div>
                        <div class="col-12">
                            <label for="alamat_ibu_wali" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat_ibu_wali" name="alamat_ibu_wali" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Data Saudara -->
            <div class="section-title">
                <i class="fas fa-user-friends"></i>
                <span>Data Saudara</span>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="saudara_kandung" class="form-label">Saudara Kandung</label>
                    <textarea class="form-control" id="saudara_kandung" name="saudara_kandung" rows="3" placeholder="Nama, usia, pendidikan/pekerjaan"></textarea>
                </div>
                <div class="col-md-4">
                    <label for="saudara_bapak" class="form-label">Saudara dari Bapak</label>
                    <textarea class="form-control" id="saudara_bapak" name="saudara_bapak" rows="3" placeholder="Nama, usia, pendidikan/pekerjaan"></textarea>
                </div>
                <div class="col-md-4">
                    <label for="saudara_ibu" class="form-label">Saudara dari Ibu</label>
                    <textarea class="form-control" id="saudara_ibu" name="saudara_ibu" rows="3" placeholder="Nama, usia, pendidikan/pekerjaan"></textarea>
                </div>
            </div>
            
            <!-- Kegiatan -->
            <div class="section-title">
                <i class="fas fa-running"></i>
                <span>Kegiatan</span>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="organisasi" class="form-label">Organisasi</label>
                    <textarea class="form-control" id="organisasi" name="organisasi" rows="3" placeholder="Nama organisasi, jabatan, periode"></textarea>
                </div>
                <div class="col-md-4">
                    <label for="olahraga" class="form-label">Olahraga</label>
                    <textarea class="form-control" id="olahraga" name="olahraga" rows="3" placeholder="Jenis olahraga, prestasi"></textarea>
                </div>
                <div class="col-md-4">
                    <label for="kesenian" class="form-label">Kesenian</label>
                    <textarea class="form-control" id="kesenian" name="kesenian" rows="3" placeholder="Jenis kesenian, prestasi"></textarea>
                </div>
            </div>
            
            <!-- Lainnya -->
            <div class="section-title">
                <i class="fas fa-ellipsis-h"></i>
                <span>Lainnya</span>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="tidak_pernah_dipidana" name="tidak_pernah_dipidana" value="1" checked>
                        <label class="form-check-label" for="tidak_pernah_dipidana">
                            Saya tidak pernah dipidana karena melakukan suatu kejahatan
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terikat_instansi_lain" name="terikat_instansi_lain" value="1">
                        <label class="form-check-label" for="terikat_instansi_lain">
                            Saya sedang terikat dengan instansi lain
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="catatan" class="form-label">Catatan Lainnya</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Informasi tambahan lainnya"></textarea>
                </div>
            </div>
            
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <div class="btn-group-custom">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Simpan Data</button>
                        <button type="reset" class="btn btn-secondary btn-lg"><i class="fas fa-redo"></i> Reset Form</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced draft management functions for the form

        // Auto-save draft (simpan ke localStorage)
        function saveDraft() {
            const formData = new FormData(document.getElementById('riwayatHidupForm'));
            const draftData = {};
            
            for (let [key, value] of formData.entries()) {
                draftData[key] = value;
            }
            
            // Tambahkan timestamp untuk tracking
            draftData._timestamp = new Date().toISOString();
            draftData._version = '1.0';
            
            localStorage.setItem('riwayat_hidup_draft', JSON.stringify(draftData));
            
            // Tampilkan indikator auto-save
            showAutoSaveIndicator();
        }

        // Load draft dengan konfirmasi
        function loadDraft() {
            const draft = localStorage.getItem('riwayat_hidup_draft');
            if (draft) {
                const draftData = JSON.parse(draft);
                const timestamp = draftData._timestamp;
                
                // Tampilkan notifikasi draft dengan informasi timestamp
                const draftTime = timestamp ? new Date(timestamp).toLocaleString('id-ID') : 'Tidak diketahui';
                
                const draftNotification = `
                    <div class="alert alert-info alert-dismissible fade show" role="alert" id="draftAlert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-save me-2"></i>
                            <div class="flex-grow-1">
                                <strong>Draft Ditemukan!</strong><br>
                                <small>Tersimpan pada: ${draftTime}</small>
                            </div>
                            <div class="btn-group ms-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="confirmLoadDraft()">
                                    <i class="fas fa-upload"></i> Muat Draft
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmClearDraft()">
                                    <i class="fas fa-trash"></i> Hapus Draft
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                document.querySelector('.info-card').insertAdjacentHTML('afterend', draftNotification);
            }
        }

        // Konfirmasi loading draft
        function confirmLoadDraft() {
            if (confirm('Apakah Anda ingin memuat data draft? Data yang sudah diisi di form akan ditimpa.')) {
                loadDraftData();
                document.getElementById('draftAlert').remove();
            }
        }

        // Load data draft ke form
        function loadDraftData() {
            const draft = localStorage.getItem('riwayat_hidup_draft');
            if (draft) {
                const draftData = JSON.parse(draft);
                let fieldsLoaded = 0;
                
                for (let [key, value] of Object.entries(draftData)) {
                    // Skip metadata fields
                    if (key.startsWith('_')) continue;
                    
                    const field = document.querySelector(`[name="${key}"]`);
                    if (field) {
                        if (field.type === 'checkbox') {
                            field.checked = value === '1';
                        } else {
                            field.value = value;
                        }
                        fieldsLoaded++;
                    }
                }
                
                // Tampilkan notifikasi sukses
                showNotification('success', `Draft berhasil dimuat! ${fieldsLoaded} field telah diisi.`);
                
                // Scroll ke atas form
                document.querySelector('.header').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Konfirmasi hapus draft
        function confirmClearDraft() {
            const modalHtml = `
                <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Konfirmasi Hapus Draft
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                                </div>
                                <p class="text-center"><strong>Apakah Anda yakin ingin menghapus draft?</strong></p>
                                <p class="text-muted text-center">
                                    Data draft yang tersimpan akan dihapus permanen dan tidak dapat dikembalikan.
                                </p>
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Pastikan Anda sudah menyimpan data penting sebelum menghapus draft.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Batal
                                </button>
                                <button type="button" class="btn btn-danger" onclick="clearDraft()" data-bs-dismiss="modal">
                                    <i class="fas fa-trash me-1"></i> Hapus Draft
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Hapus modal lama jika ada
            const existingModal = document.getElementById('confirmDeleteModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Tambahkan modal baru
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Tampilkan modal
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            confirmModal.show();
        }

        // Hapus draft
        function clearDraft() {
            try {
                localStorage.removeItem('riwayat_hidup_draft');
                
                // Hapus notifikasi draft jika ada
                const draftAlert = document.getElementById('draftAlert');
                if (draftAlert) {
                    draftAlert.remove();
                }
                
                // Tampilkan notifikasi sukses
                showNotification('success', 'Draft berhasil dihapus!');
                
                // Hapus indikator auto-save
                const autoSaveIndicator = document.getElementById('autoSaveIndicator');
                if (autoSaveIndicator) {
                    autoSaveIndicator.remove();
                }
                
            } catch (error) {
                console.error('Error clearing draft:', error);
                showNotification('error', 'Gagal menghapus draft. Silakan coba lagi.');
            }
        }

        // Tampilkan notifikasi
        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            
            const notification = `
                <div class="alert ${alertClass} alert-dismissible fade show notification-temp" role="alert">
                    <i class="fas fa-${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.querySelector('.header').insertAdjacentHTML('afterend', notification);
            
            // Auto hide setelah 5 detik
            setTimeout(() => {
                const tempNotif = document.querySelector('.notification-temp');
                if (tempNotif) {
                    tempNotif.remove();
                }
            }, 5000);
        }

        // Tampilkan indikator auto-save
        function showAutoSaveIndicator() {
            // Hapus indikator lama
            const existingIndicator = document.getElementById('autoSaveIndicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            const indicator = `
                <div id="autoSaveIndicator" class="position-fixed" style="bottom: 20px; right: 20px; z-index: 1050;">
                    <div class="badge bg-success p-2">
                        <i class="fas fa-save me-1"></i>
                        Draft tersimpan
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', indicator);
            
            // Auto hide setelah 2 detik
            setTimeout(() => {
                const autoSaveIndicator = document.getElementById('autoSaveIndicator');
                if (autoSaveIndicator) {
                    autoSaveIndicator.style.opacity = '0';
                    autoSaveIndicator.style.transition = 'opacity 0.3s';
                    setTimeout(() => autoSaveIndicator.remove(), 300);
                }
            }, 2000);
        }

        // Cek apakah form sudah berubah untuk auto-save
        let formChanged = false;
        let autoSaveTimeout;

        function trackFormChanges() {
            const inputs = document.querySelectorAll('input, textarea, select');
            
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    formChanged = true;
                    
                    // Hapus timeout sebelumnya
                    clearTimeout(autoSaveTimeout);
                    
                    // Set timeout baru untuk auto-save
                    autoSaveTimeout = setTimeout(() => {
                        if (formChanged) {
                            saveDraft();
                            formChanged = false;
                        }
                    }, 2000); // Auto-save 2 detik setelah user berhenti mengetik
                });
                
                // Hapus kelas is-invalid saat user mulai mengisi
                input.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.classList.remove('is-invalid');
                    }
                });
            });
        }

        // Cek ukuran draft
        function getDraftSize() {
            const draft = localStorage.getItem('riwayat_hidup_draft');
            if (draft) {
                const sizeInBytes = new Blob([draft]).size;
                const sizeInKB = (sizeInBytes / 1024).toFixed(2);
                return `${sizeInKB} KB`;
            }
            return '0 KB';
        }

        // Tampilkan info draft di console (untuk debugging)
        function showDraftInfo() {
            const draft = localStorage.getItem('riwayat_hidup_draft');
            if (draft) {
                const draftData = JSON.parse(draft);
                console.log('Draft Info:');
                console.log('- Size:', getDraftSize());
                console.log('- Timestamp:', draftData._timestamp);
                console.log('- Fields:', Object.keys(draftData).filter(key => !key.startsWith('_')).length);
            } else {
                console.log('No draft found');
            }
        }

        // Export functions for manual use
        window.draftManager = {
            save: saveDraft,
            load: loadDraftData,
            clear: clearDraft,
            info: showDraftInfo,
            size: getDraftSize
        };

        // Validasi form sebelum submit
        document.getElementById('riwayatHidupForm').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = document.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi!');
                // Scroll ke field pertama yang error
                const firstInvalid = document.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                // Tampilkan loading
                const submitBtn = document.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                submitBtn.disabled = true;
                
                // Hapus draft setelah berhasil submit
                localStorage.removeItem('riwayat_hidup_draft');
                
                // Reset button setelah 5 detik jika terjadi error
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Load draft saat halaman dimuat
            loadDraft();
            
            // Track form changes untuk auto-save
            trackFormChanges();
            
            // Auto-scroll ke success message jika ada
            <?php if ($showPrint): ?>
            setTimeout(() => {
                document.querySelector('.print-success').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 500);
            <?php endif; ?>
            
            // Auto-save setiap 30 detik jika ada perubahan
            setInterval(() => {
                if (formChanged) {
                    saveDraft();
                    formChanged = false;
                }
            }, 30000);
        });

        // Simpan draft saat user meninggalkan halaman
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                saveDraft();
            }
        });

        // Preview data sebelum submit
        function previewData() {
            const formData = new FormData(document.getElementById('riwayatHidupForm'));
            let previewContent = '<h4>Preview Data Riwayat Hidup</h4><hr>';
            
            previewContent += '<h5>Data Diri:</h5>';
            previewContent += '<p><strong>Nama:</strong> ' + (formData.get('nama_lengkap') || '-') + '</p>';
            previewContent += '<p><strong>Agama:</strong> ' + (formData.get('agama') || '-') + '</p>';
            previewContent += '<p><strong>Jenis Kelamin:</strong> ' + (formData.get('jenis_kelamin') || '-') + '</p>';
            previewContent += '<p><strong>Tempat/Tanggal Lahir:</strong> ' + (formData.get('tempat_lahir') || '-') + ', ' + (formData.get('tanggal_lahir') || '-') + '</p>';
            
            // Buat modal untuk preview
            const modalHtml = `
                <div class="modal fade" id="previewModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Preview Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${previewContent}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('riwayatHidupForm').submit()">Simpan Data</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Hapus modal lama jika ada
            const existingModal = document.getElementById('previewModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Tambahkan modal baru
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }

        // Fungsi untuk mencetak ulang data yang sudah tersimpan
        function reprintData() {
            const lastId = <?php echo $lastInsertId ?: 0; ?>;
            if (lastId > 0) {
                window.open('?print=1&id=' + lastId, '_blank');
            }
        }
    </script>
</body>
</html>