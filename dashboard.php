<?php
session_start();

if (!isset($_SESSION['id_users']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Set Timezone agar sinkron
date_default_timezone_set('Asia/Jakarta');

$msg = "";
if (isset($_POST['upload_kegiatan'])) {
    $judul = htmlspecialchars($_POST['judul']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    
    // Logic Upload Gambar
    $targetDir = "uploads/kegiatan/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES["gambar"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');

    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $targetFilePath)) {
            $stmt = $pdo->prepare("INSERT INTO dokumentasi (judul, deskripsi, gambar) VALUES (?, ?, ?)");
            if ($stmt->execute([$judul, $deskripsi, $fileName])) {
                $msg = "<div class='alert alert-success'>Kegiatan berhasil dipublikasikan!</div>";
            }
        } else {
            $msg = "<div class='alert alert-danger'>Maaf, terjadi error saat upload gambar.</div>";
        }
    } else {
        $msg = "<div class='alert alert-warning'>Format file tidak didukung (Gunakan JPG/PNG).</div>";
    }
}
// --- END LOGIC CMS ---

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'nasabah'");
    $totalNasabah = $stmt->fetch()['total'];

    // Total transaksi
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM transaksi");
    $totalTransaksi = $stmt->fetch()['total'] ?? 0;

    // Total berat sampah
    $stmt = $pdo->query("SELECT SUM(berat) as total FROM transaksi WHERE status = 'selesai'");
    $totalBerat = $stmt->fetch()['total'] ?? 0;

    // Total saldo
    $stmt = $pdo->query("SELECT SUM(saldo) as total FROM transaksi WHERE status = 'selesai'");
    $totalPoin = $stmt->fetch()['total'] ?? 0;

    // 2. Logic JOIN: Menggunakan id_users (sesuai struktur DB Anda)
    $stmt = $pdo->prepare("
        SELECT t.*, u.nama as nama_nasabah, s.nama_sampah as jenis_sampah
        FROM transaksi t
        JOIN users u ON t.id_users = u.id_users
        JOIN jenis_sampah s ON t.id_sampah = s.id_sampah
        ORDER BY t.tanggal DESC LIMIT 5
    ");
    $stmt->execute();
    $recentTransactions = $stmt->fetchAll();

} catch(PDOException $e) {
    $totalTransaksi = 0; $totalBerat = 0; $totalPoin = 0;
    $recentTransactions = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Bank Sampah Manyar21</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    <style>
    :root {
        --sidebar-bg: #ffffff;
        --sidebar-border: #e9ecef;
        --text-main: #495057;
        --text-active: #0d6efd;
        --hover-bg: #f8f9fa;
        --body-bg: #f4f7f6;
    }

    body {
        background-color: #f8f9fa; /* Abu-abu sangat muda untuk background luar */
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
    }

    /* Sidebar Professional Clean */
    .sidebar { 
        height: 100vh; 
        background: #ffffff !important; /* Putih bersih */
        color: #495057 !important; 
        position: fixed; 
        top: 0;
        left: 0;
        width: 240px; 
        z-index: 1000;
        overflow-y: auto;
        padding: 1.5rem 1rem;
        /* Tambahkan border halus di kanan sebagai pengganti gradasi */
        border-right: 1px solid #e9ecef; 
    }

    .sidebar-brand {
        font-size: 1.25rem;
        font-weight: 800;
        color: #212529;
        margin-bottom: 2rem;
        padding: 0 0.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: #adb5bd;
        margin: 20px 0 8px 10px;
    }

    .sidebar .nav-link { 
        color: #6c757d !important; /* Abu-abu medium */
        font-weight: 500;
        border-radius: 10px;
        margin-bottom: 5px;
        padding: 10px 15px;
        transition: all 0.2s ease;
    }

    .sidebar .nav-link i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .sidebar .nav-link.text-danger:hover {
        background-color: #fff5f5 !important;
    }

    .sidebar .nav-link:hover { 
        background-color: #f1f3f5 !important;
        color: #0d6efd !important; /* Biru modern saat hover */
    }

    .sidebar .nav-link.active { 
        background-color: #e7f1ff !important; /* Biru sangat muda saat aktif */
        color: #0d6efd !important;
        font-weight: 600;
    }

    /* Penyesuaian Main Content */
    main { 
        margin-left: 250px; 
        padding: 2rem; 
    }

    /* Card Stat Tetap Menggunakan Gradasi Agar Eye-Catching */
    .card { 
        border: 1px solid #edf2f7 !important; 
        border-radius: 15px; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; 
        background: #ffffff;
    }

    .table th { 
        background: #f8f9fa !important; 
        color: #495057 !important; 
        font-weight: 600;
        border-bottom: 2px solid #dee2e6 !important;
    }

    .card:hover { transform: translateY(-3px); }
    .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: white; }
    
    .bg-primary-custom { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .bg-success-custom { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .bg-warning-custom { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
</style>
</style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <h5 class="mb-4"><i class="fas fa-recycle"></i> Admin Panel</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="nasabah.php">
                                <i class="fas fa-users"></i> Nasabah
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="jenis_sampah.php">
                                <i class="fas fa-recycle"></i> Jenis Sampah
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="transaksi.php">
                                <i class="fas fa-history"></i> Transaksi
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="setor_sampah.php">
                                <i class="fas fa-plus-circle"></i> Setor Sampah
                            </a>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="dokumentasi.php">
                                <i class="fas fa-camera"></i> Dokumentasi CMS
                            </a>
                        </li>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Admin BSM 21</h2>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle me-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card"><div class="card-body"><div class="d-flex align-items-center"><div class="stat-icon bg-primary-custom text-white me-3"><i class="fas fa-users"></i></div>
                        <div><h4 class="mb-0"><?php echo number_format($totalNasabah); ?></h4><small class="text-muted">Total Nasabah</small></div></div></div></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card"><div class="card-body"><div class="d-flex align-items-center"><div class="stat-icon bg-success-custom text-white me-3"><i class="fas fa-exchange-alt"></i></div>
                        <div><h4 class="mb-0"><?php echo number_format($totalTransaksi); ?></h4><small class="text-muted">Total Transaksi</small></div></div></div></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card"><div class="card-body"><div class="d-flex align-items-center"><div class="stat-icon bg-warning-custom text-white me-3"><i class="fas fa-weight"></i></div>
                        <div><h4 class="mb-0"><?php echo number_format($totalBerat, 1); ?> kg</h4><small class="text-muted">Total Berat Sampah</small></div></div></div></div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card"><div class="card-body"><div class="d-flex align-items-center"><div class="stat-icon bg-info-custom text-white me-3"><img src="./img/icons8-coin-48.png" alt=""></div>
                        <div><h4 class="mb-0"><?php echo number_format($totalPoin); ?></h4><small class="text-muted">Total Saldo</small></div></div></div></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-clock"></i> Transaksi Terbaru</h5></div>
                    <div class="card-body">
                        <?php if (empty($recentTransactions)): ?>
                            <p class="text-muted mb-0">Belum ada transaksi</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nasabah</th>
                                            <th>Jenis Sampah</th>
                                            <th>Berat</th>
                                            <th>Saldo (Rp)</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTransactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($transaction['nama_nasabah']); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['jenis_sampah']); ?></td>
                                                <td><?php echo number_format($transaction['berat'], 1); ?> kg</td>
                                                <td><?php echo number_format($transaction['saldo']); ?></td>
                                                <td><span class="badge bg-<?php echo $transaction['status'] == 'selesai' ? 'success' : 'warning'; ?>"><?php echo ucfirst($transaction['status']); ?></span></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($transaction['tanggal'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div> </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>