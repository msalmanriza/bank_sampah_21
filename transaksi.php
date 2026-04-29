<?php
session_start();

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['id_users']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_status') {
        $id = $_POST['id']; 
        $status = $_POST['status'];

        try {
            $stmt = $pdo->prepare("UPDATE transaksi SET status = ? WHERE id_transaksi = ?");
            $stmt->execute([$status, $id]);

            $message = 'Status transaksi berhasil diupdate!';
            $messageType = 'success';
        } catch(PDOException $e) {
            $message = 'Gagal mengupdate status transaksi!';
            $messageType = 'danger';
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "
    SELECT 
        t.*, 
        u.nama as nama_nasabah, 
        COALESCE(nama_sampah) as display_jenis_sampah
    FROM transaksi t
    JOIN users u ON t.id_users = u.id_users 
    LEFT JOIN jenis_sampah s ON t.id_sampah = s.id_sampah
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $query .= " AND t.status = ?";
    $params[] = $status_filter;
}
if ($date_from) {
    $query .= " AND DATE(t.tanggal) >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $query .= " AND DATE(t.tanggal) <= ?";
    $params[] = $date_to;
}
if ($search) {
    $query .= " AND (u.nama LIKE ? OR t.jenis_transaksi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY t.tanggal DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $transaksi = $stmt->fetchAll();

    // Statistik Ringkas
    $totalTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn() ?: 0;
    $pendingTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'pending'")->fetchColumn() ?: 0;
    $selesaiTransaksi = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE status = 'selesai'")->fetchColumn() ?: 0;
    
    // Perbaikan: Menghitung Total Berat dari kolom 'berat'
    $totalBerat = $pdo->query("SELECT SUM(berat) FROM transaksi WHERE status = 'selesai'")->fetchColumn() ?: 0;
    
    // Perbaikan: Menghitung Total Saldo dari kolom 'saldo'
    $totalSaldo = $pdo->query("SELECT SUM(saldo) FROM transaksi WHERE status = 'selesai'")->fetchColumn() ?: 0;

} catch (PDOException $e) {
    $message = "Error Database: " . $e->getMessage();
    $messageType = "danger";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Bank Sampah Manyar21</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <style>
    body {
        background-color: #f4f7f6; 
        font-family: 'Inter', 'Segoe UI', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

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
        border-right: 1px solid #e9ecef; /* Garis pemisah halus */
    }

    .sidebar .nav-link { 
    color: #6c757d !important; /* Contoh warna abu-abu */
    font-weight: 500;
    border-radius: 10px;
    margin-bottom: 5px;
    padding: 10px 15px !important; /* Menambah ruang di dalam link */
    display: flex !important;       /* Membuat icon dan text berjejer rapi */
    align-items: center !important; /* Memastikan icon dan text sejajar vertical */
    }

    .sidebar .nav-link i {
    margin-right: 12px !important; /* INI KUNCI AGAR TIDAK MEPET (minimal 10px) */
    width: 20px !important;        /* Menyamakan lebar icon agar text rapi lurus */
    text-align: center !important;  /* Menengahkan icon dalam lebarnya */
    }

    /* Efek Hover & Menu Aktif */
    .sidebar .nav-link:hover { 
        background-color: #f1f3f5 !important;
        color: #0d6efd !important; 
    }

    .sidebar .nav-link.active { 
    background-color: #e7f1ff !important; /* Biru muda transparan agar menyala */
    color: #0d6efd !important;          /* Teks biru tegas */
    font-weight: 600;                   /* Teks lebih tebal */
    border-right: 4px solid #0d6efd;    /* Opsional: Tambah garis penanda di kanan agar lebih keren */
}

    main, .main-content { 
        margin-left: 240px; 
        padding: 40px;
        width: calc(100% - 240px);
        min-height: 100vh;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
        min-width: 80px;
    }

    .status-selesai {
        background-color: rgba(40, 167, 69, 0.1); /* Hijau transparan */
        color: #28a745; /* Teks Hijau */
        border: 1.5px solid #28a745; /* Border Hijau */
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1.5px solid #ffc107;
    }

    .status-ditolak {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1.5px solid #dc3545;
    }

    /* --- INI PERBAIKANNYA: MEMISAHKAN STYLE CARD --- */

    .row .card { 
        background: #5323ff !important; /* Warna Ungu Modern */
        border: none !important;
        border-radius: 15px !important; 
        box-shadow: 0 4px 15px rgba(119, 98, 255, 0.25) !important;
        margin-bottom: 20px;
        color: white !important;
        transition: transform 0.2s;
    }

    .row .card:hover { transform: translateY(-3px); }

    .row .card h3, 
    .row .card h5,
    .row .card p, 
    .row .card span, 
    .row .card i,
    .row .card .card-title,
    .row .card .card-text { 
        color: white !important; 
    }

    .card-stats { 
        background: #7762ff !important; 
        border: none !important;
        border-radius: 15px !important; 
        box-shadow: 0 4px 15px rgba(119, 98, 255, 0.25) !important;
        color: white !important;
        margin-bottom: 25px;
    }

    .card-stats h3, .card-stats p, .card-stats span, .card-stats i { 
        color: white !important; 
    }

    .card-table-clean { 
        background: #ffffff !important; /* Paksa warna putih bersih */
        border: 1px solid #e2e8f0 !important; 
        border-radius: 12px !important; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important;
        overflow: hidden;
        margin-top: 20px;
    }

    .card-table-clean .table {
        background: #ffffff !important;
        margin-bottom: 0;
    }

    .card-table-clean .table thead th {
        background-color: #f8f9fa !important;
        color: #475569 !important;
        border-bottom: 2px solid #f1f5f9 !important;
        font-weight: 600;
        padding: 15px !important;
    }

    .card-table-clean .table tbody td {
        background-color: #ffffff !important;
        color: #334155 !important;
        padding: 12px 15px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle;
    }

    .card-table-clean .table-hover tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    .card-stats:hover { transform: translateY(-3px); }
    .card-stats h3, .card-stats p, .card-stats span, .card-stats i { color: white !important; }

    .main-content > .card, 
    .container-fluid > .card,
    .card-table-container { 
        background: #ffffff !important; 
        border: 1px solid #e2e8f0 !important; 
        border-radius: 16px !important; 
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important; 
        padding: 20px;
        margin-top: 20px;
        color: #334155 !important; /* Teks tabel tetap gelap */
    }

    .table {
        background: #ffffff !important;
        margin-bottom: 0;
    }

    .table th { 
        background: #f8f9fa !important; 
        color: #64748b !important; 
        border-bottom: 2px solid #f1f5f9 !important;
        padding: 15px !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .table td {
        padding: 15px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155 !important;
    }
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
                    <h2>Riwayat Transaksi</h2>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <i class="fas fa-info-circle"></i> <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-md-2"><div class="card summary-card text-white"><div class="card-body text-center"><h4><?php echo number_format($totalTransaksi); ?></h4><small>Total Transaksi</small></div></div></div>
                    <div class="col-md-2"><div class="card summary-card text-white"><div class="card-body text-center"><h4><?php echo number_format($pendingTransaksi); ?></h4><small>Pending</small></div></div></div>
                    <div class="col-md-2"><div class="card summary-card text-white"><div class="card-body text-center"><h4><?php echo number_format($selesaiTransaksi); ?></h4><small>Selesai</small></div></div></div>
                    <div class="col-md-3"><div class="card summary-card text-white"><div class="card-body text-center"><h4><?php echo number_format($totalBerat, 1); ?> kg</h4><small>Total Berat</small></div></div></div>
                    <div class="col-md-3"><div class="card summary-card text-white"><div class="card-body text-center"><h4><?php echo number_format($totalSaldo); ?></h4><small>Total Saldo</small></div></div></div>
                </div>

                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="selesai" <?php echo $status_filter == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="ditolak" <?php echo $status_filter == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cari</label>
                            <input type="text" class="form-control" name="search" placeholder="Nama atau jenis sampah" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                            <a href="transaksi.php" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a> 
                           <a href="export_excel.php" class="btn btn-success" title="Download Excel Hari Ini"><i class="fas fa-file-excel"></i> Excel</a>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-statistik">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nasabah</th>
                                        <th>Jenis Sampah</th>
                                        <th>Berat</th>
                                        <th>Saldo (Rp)</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    foreach ($transaksi as $t):
                                        $status = strtolower($t['status'] ?? 'pending');
                                        
                                        // Logika warna dipaksa (forced) agar tidak tertimpa CSS lain
                                        if ($status == 'selesai') {
                                            $badge_style = "background-color: #e8f5e9 !important; color: #2e7d32 !important; border: 1.5px solid #2e7d32 !important;";
                                        } elseif ($status == 'ditolak') {
                                            $badge_style = "background-color: #ffebee !important; color: #c62828 !important; border: 1.5px solid #c62828 !important;";
                                        } else {
                                            $badge_style = "background-color: #fff3e0 !important; color: #ef6c00 !important; border: 1.5px solid #ef6c00 !important;";
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>  
                                            <td><?php echo htmlspecialchars($t['nama_nasabah']); ?></td>
                                            <td><?php echo htmlspecialchars($t['display_jenis_sampah']); ?></td>
                                            <td><?php echo number_format($t['berat'], 1); ?> kg</td>
                                            
                                            <td style="font-weight: 600; color: #28a745;">
                                                Rp <?php echo number_format($t['saldo'], 0, ',', '.'); ?>
                                            </td>

                                            <td>
                                                <span style="display: inline-block !important; padding: 5px 12px !important; border-radius: 50px !important; font-size: 12px !important; font-weight: 700 !important; text-align: center !important; min-width: 85px !important; <?= $badge_style ?>">
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($t['tanggal'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info btn-custom" onclick="viewDetail(<?php echo $t['id_transaksi']; ?>)">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>                       
                                                <?php if ($status == 'pending'): ?>
                                                    <button class="btn btn-sm btn-success btn-custom ms-1" onclick="updateStatus(<?php echo $t['id_transaksi']; ?>, 'selesai')">
                                                        <i class="fas fa-check"></i> Setujui
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btn-custom ms-1" onclick="updateStatus(<?php echo $t['id_transaksi']; ?>, 'ditolak')">
                                                        <i class="fas fa-times"></i> Tolak
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                            </tbody>
                            </table>
                        </div>
                        <?php if (empty($transaksi)): ?>
                            <div class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p class="text-muted">Tidak ada transaksi ditemukan</p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Detail Transaksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="detailContent"></div></div></div></div>
    <form id="statusForm" method="POST" style="display: none;"><input type="hidden" name="action" value="update_status"><input type="hidden" id="status_id" name="id"><input type="hidden" id="status_value" name="status"></form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewDetail(id) {
    const content = document.getElementById('detailContent');
    content.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Mengambil data...</p></div>';
    
    // Tampilkan modal terlebih dahulu
    const myModal = new bootstrap.Modal(document.getElementById('detailModal'));
    myModal.show();

    // Ambil data dari server secara async
    fetch(`get_detail_transaksi.php?id=${id}`)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan koneksi.</div>';
        });
}
        function updateStatus(id, status) {
            if (confirm(`Yakin ingin ${status === 'selesai' ? 'menyetujui' : 'menolak'} transaksi ini?`)) {
                document.getElementById('status_id').value = id;
                document.getElementById('status_value').value = status;
                document.getElementById('statusForm').submit();
            }
        }
    </script>
</body>
</html>