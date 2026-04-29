<?php
date_default_timezone_set('Asia/Jakarta');
include 'config.php'; 

// Setting Header agar browser mengenali ini sebagai file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Transaksi_Hari_Ini.xls");
header("Pragma: no-cache");
header("Expires: 0");

?>
<table border="1">
    <tr>
        <th colspan="7" style="background-color: #4F81BD; color: white;">LAPORAN TRANSAKSI MINGGUAN - BANK SAMPAH MANYAR21</th>
    </tr>
    <tr>
        <th colspan="7">Tanggal Cetak: <?php echo date('d-m-Y H:i'); ?></th>
    </tr>
    <tr style="background-color: #DCE6F1;">
        <th>No</th> <th>Nama Nasabah</th>
        <th>Jenis Sampah</th>
        <th>Berat (kg)</th>
        <th>Saldo (Rp)</th>
        <th>Status</th>
        <th>Tanggal</th>
    </tr>

    <?php
    if (isset($pdo)) {
        $hari_ini = date('Y-m-d');
        $query = "
            SELECT t.*, u.nama as nama_nasabah, 
            COALESCE(nama_sampah) as display_jenis_sampah
            FROM transaksi t
            JOIN users u ON t.id_users = u.id_users 
            LEFT JOIN jenis_sampah s ON t.id_sampah = s.id_sampah
            WHERE DATE(t.tanggal) = ?
            ORDER BY t.tanggal DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$hari_ini]);
        $data = $stmt->fetchAll();

        if (count($data) > 0) {
            $no = 1; // 2. Inisialisasi variabel nomor urut
            foreach ($data as $t):
    ?>
    <tr>
        <td align="center"><?php echo $no++; ?></td>
        <td><?php echo htmlspecialchars($t['nama_nasabah']); ?></td>
        <td><?php echo htmlspecialchars($t['display_jenis_sampah']); ?></td>
        <td align="center"><?php echo number_format($t['berat'], 1); ?></td>
        <td align="right"><?php echo number_format($t['saldo'], 0, ',', '.'); ?></td>
        <td align="center"><?php echo ucfirst($t['status']); ?></td>
        <td><?php echo $t['tanggal']; ?></td>
    </tr>
    <?php 
            endforeach;
        } else {
            echo '<tr><td colspan="7" align="center">Tidak ada transaksi untuk hari ini.</td></tr>';
        }
    } else {
        echo '<tr><td colspan="7" align="center">Koneksi database gagal. Periksa file config.php.</td></tr>';
    }
    ?>
</table>