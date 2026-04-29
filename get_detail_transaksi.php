<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("
            SELECT t.*, u.nama as nama_nasabah, s.nama_sampah 
            FROM transaksi t
            JOIN users u ON t.id_users = u.id_users
            LEFT JOIN jenis_sampah s ON t.id_sampah = s.id_sampah
            WHERE t.id_transaksi = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            echo '
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><td><strong>ID Transaksi</strong></td><td>: #'.$data['id_transaksi'].'</td></tr>
                        <tr><td><strong>Nasabah</strong></td><td>: '.$data['nama_nasabah'].'</td></tr>
                        <tr><td><strong>Tanggal</strong></td><td>: '.date('d M Y H:i', strtotime($data['tanggal'])).'</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr><td><strong>Jenis Sampah</strong></td><td>: '.$data['nama_sampah'].'</td></tr>
                        <tr><td><strong>Berat</strong></td><td>: '.$data['berat'].' kg</td></tr>
                        <tr><td><strong>Saldo didapat</strong></td><td>: <span class="text-success fw-bold">'.$data['saldo'].' Rp</span></td></tr>
                    </table>
                </div>
            </div>
            <hr>
            <div class="alert alert-info">
                Status Saat Ini: <strong>'.ucfirst($data['status']).'</strong>
            </div>';
        }
    } catch(PDOException $e) {
        echo "Gagal memuat data.";
    }
}
?>