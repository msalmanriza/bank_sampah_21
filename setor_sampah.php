<?php
session_start();
// 1. Set timezone di level PHP
date_default_timezone_set('Asia/Jakarta');

// Proteksi Session Admin
if (!isset($_SESSION['id_users']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// 2. Sinkronisasi timezone di level Database Session
// Ini memastikan MySQL menggunakan UTC+7 (WIB) untuk transaksi ini
$pdo->exec("SET time_zone = '+07:00'");

$message = '';
$messageType = '';

// 3. Logic Simpan Setoran
// 3. Logic Simpan Setoran (Update untuk Multi-Input)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_setor'])) {
    $id_users = $_POST['id_users'];
    $items_sampah = $_POST['id_sampah']; // Ini akan jadi array
    $items_berat = $_POST['berat'];    // Ini akan jadi array
    $id_admin = $_SESSION['id_users'];
    $tanggal_sekarang = date('Y-m-d H:i:s');
    $total_poin_transaksi = 0;

    try {
        $pdo->beginTransaction(); // Gunakan transaction agar data aman

        foreach ($items_sampah as $index => $id_sampah) {
            $berat = $items_berat[$index];
            
            if ($berat <= 0) continue;

            // Ambil harga
            $stmtHarga = $pdo->prepare("SELECT harga_perkg FROM jenis_sampah WHERE id_sampah = ?");
            $stmtHarga->execute([$id_sampah]);
            $dataSampah = $stmtHarga->fetch();

            if ($dataSampah) {
                $total_harga = $berat * $dataSampah['harga_perkg'];
                $total_poin_transaksi += $total_harga;

                // Insert ke setor_sampah
                $sqlSetor = "INSERT INTO setor_sampah (id_users, id_admin, id_sampah, berat, total_harga, tanggal_setor) 
                             VALUES (?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sqlSetor)->execute([$id_users, $id_admin, $id_sampah, $berat, $total_harga, $tanggal_sekarang]);

                // Insert ke transaksi
                $sqlTrans = "INSERT INTO transaksi (id_users, id_sampah, berat, saldo, status, tanggal) 
                             VALUES (?, ?, ?, ?, 'selesai', ?)";
                $pdo->prepare($sqlTrans)->execute([$id_users, $id_sampah, $berat, $total_harga, $tanggal_sekarang]);
            }
        }

        $pdo->commit();
        $message = "Setoran multi-item berhasil dicatat! Total Poin: " . number_format($total_poin_transaksi, 0, ',', '.');
        $messageType = "success";
    } catch(Exception $e) {
        $pdo->rollBack();
        $message = "Gagal mencatat: " . $e->getMessage();
        $messageType = "danger";
    }
}

// 5. Ambil Data Nasabah & Jenis Sampah untuk Dropdown
$nasabah = $pdo->query("SELECT id_users, nama FROM users WHERE role = 'nasabah'")->fetchAll();
$sampah = $pdo->query("SELECT * FROM jenis_sampah")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Sampah - Bank Sampah Manyar21</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* Base Background */
    body {
        background-color: #f8f9fa; 
        font-family: 'Inter', 'Segoe UI', sans-serif;
        overflow-x: hidden; /* Mencegah scroll samping */
        margin: 0;
    }

    main, .main-content { 
        margin-left: 240px; /* Jarak tetap untuk sidebar */
        padding: 40px; 
        min-height: 100vh;
        width: calc(100% - 240px); /* Memastikan lebar hanya sisa layar */
        display: block; /* Memastikan bukan flex di level ini agar tidak ke tengah */
    }

    .row-content {
        display: flex;
        justify-content: flex-start; /* Memaksa konten mulai dari kiri */
        align-items: flex-start;
        gap: 20px;
        width: 100%;
    }

    /* Responsif: Jika layar kecil */
    @media (max-width: 768px) {
        .sidebar { width: 0; display: none; }
        main, .container-fluid, .main-content { 
            margin-left: 0; 
            padding: 15px; 
            width: 100%; /* Lebar penuh saat sidebar hilang */
        }
    }

    /* Sidebar Modern Minimalis */
    .sidebar { 
        height: 100vh; 
        background: #ffffff !important; 
        position: fixed; 
        top: 0; left: 0; width: 240px; 
        z-index: 1000;
        border-right: 1px solid #e9ecef;
        padding: 1.5rem 1rem;
    }

    /* --- Kode lainnya (nav-link, card, btn-custom, dll) tetap sama --- */
    .sidebar .nav-link { 
        color: #6c757d !important; 
        font-weight: 500;
        border-radius: 10px;
        margin-bottom: 5px;
        padding: 10px 15px !important; 
        display: flex !important; 
        align-items: center !important; 
    }

    .sidebar .nav-link i {
        margin-right: 12px !important; 
        width: 20px !important; 
        text-align: center !important; 
    }

    .sidebar .nav-link:hover { 
        background-color: #f1f3f5 !important;
        color: #0d6efd !important; 
    }

    .card { 
        border: 1px solid #edf2f7 !important; 
        border-radius: 15px; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; 
        background: #ffffff;
    }

    .btn-custom { 
        border-radius: 10px; 
        padding: 8px 16px; 
        font-weight: 500; 
    }

    .table th { 
        background: #f8f9fa !important; 
        color: #495057 !important; 
        font-weight: 600;
        border-bottom: 2px solid #dee2e6 !important;
        border-top: none !important;
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
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="nasabah.php"><i class="fas fa-users"></i> Nasabah</a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="jenis_sampah.php"><i class="fas fa-recycle"></i> Jenis Sampah</a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="transaksi.php"><i class="fas fa-history"></i> Transaksi</a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link active" href="setor_sampah.php"><i class="fas fa-plus-circle"></i> Setor Sampah</a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 px-4 py-4">
                <h2 class="mb-4">Input Setoran Sampah</h2>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
    <div class="col-md-7">
        <div class="card p-4">
            <form method="POST" id="formSetor">
                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Nasabah</label>
                    <select name="id_users" class="form-select" required>
                        <option value="">-- Pilih Nasabah --</option>
                        <?php foreach($nasabah as $n): ?>
                            <option value="<?= $n['id_users'] ?>"><?= $n['nama'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="sampah-container">
                    <div class="sampah-item border-bottom mb-3 pb-3">
                        <div class="row">
                            <div class="col-md-7 mb-2">
                                <label class="form-label small">Jenis Sampah</label>
                                <select name="id_sampah[]" class="form-select" required>
                                    <option value="">-- Pilih Sampah --</option>
                                    <?php foreach($sampah as $s): ?>
                                        <option value="<?= $s['id_sampah'] ?>"><?= $s['nama_sampah'] ?> (Rp <?= number_format($s['harga_perkg']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5 mb-2">
                                <label class="form-label small">Berat (Kg)</label>
                                <input type="number" step="0.01" name="berat[]" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="btnTambahSampah" class="btn btn-outline-secondary w-100 mb-3 border-dashed">
                    <i class="fas fa-plus me-2"></i> Tambah Jenis Sampah Lain
                </button>

                <button type="submit" name="proses_setor" class="btn btn-primary w-100 py-2 shadow">
                    <i class="fas fa-check-circle me-2"></i> Selesaikan & Simpan Setoran
                </button>
            </form>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card p-4 bg-light border-0">
            <h5><i class="fas fa-info-circle text-primary"></i> Instruksi Admin</h5>
            <ul class="mt-3">
                <li>Pilih nama nasabah yang datang membawa sampah.</li>
                <li>Gunakan tombol <strong>"Tambah Jenis Sampah Lain"</strong> jika nasabah membawa lebih dari satu jenis sampah.</li>
                <li>Masukkan berat dalam satuan Kilogram (kg).</li>
                <li>Klik <strong>"Simpan Setoran"</strong> untuk memproses semua item sekaligus ke riwayat transaksi.</li>
            </ul>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.getElementById('btnTambahSampah').addEventListener('click', function() {
    const container = document.getElementById('sampah-container');
    const firstItem = container.querySelector('.sampah-item');
    const newItem = firstItem.cloneNode(true);
    
    // Reset nilai input di item baru
    newItem.querySelectorAll('input').forEach(input => input.value = '');
    newItem.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
    
    // Tambahkan tombol hapus untuk item tambahan
    const deleteBtn = document.createElement('div');
    deleteBtn.innerHTML = `<button type="button" class="btn btn-sm btn-danger mt-1 mb-2 float-end btn-hapus"><i class="fas fa-trash"></i> Hapus Baris</button>`;
    newItem.appendChild(deleteBtn);

    container.appendChild(newItem);

    // Logic Hapus
    newItem.querySelector('.btn-hapus').addEventListener('click', function() {
        newItem.remove();
    });
});
</script>
</body>
</html>