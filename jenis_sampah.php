<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi pesan
$message = ""; 
$messageType = "";

// Ambil status dari URL untuk notifikasi
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = "Jenis sampah berhasil ditambahkan!";
        $messageType = "success";
    } elseif ($_GET['status'] == 'deleted') {
        $message = "Jenis sampah berhasil dihapus!";
        $messageType = "success";
    } elseif ($_GET['status'] == 'updated') {
        $message = "Data sampah berhasil diperbarui!";
        $messageType = "success";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // A. LOGIC DELETE
        if (isset($_POST['delete_jenis_sampah'])) {
            $id = $_POST['id'];
            $pdo->beginTransaction();
            
            // Hapus riwayat di tabel setor_sampah (Gunakan id_sampah sesuai DB Anda)
            $pdo->prepare("DELETE FROM setor_sampah WHERE id_sampah = ?")->execute([$id]);
            
            // Hapus data utama di jenis_sampah
            $stmt = $pdo->prepare("DELETE FROM jenis_sampah WHERE id_sampah = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            header("Location: jenis_sampah.php?status=deleted");
            exit();

        // B. LOGIC CREATE & UPDATE
        } elseif (isset($_POST['action'])) {
            $action = $_POST['action'];
            $jenis = trim($_POST['jenis']);
            $harga = floatval($_POST['harga_per_kg']);
            $kategori = $_POST['kategori'] ?? '';

            if ($action == 'create') {
                $stmt = $pdo->prepare("INSERT INTO jenis_sampah (nama_sampah, harga_perkg, kategori) VALUES (?, ?, ?)");
                $stmt->execute([$jenis, $harga, $kategori]);
                header("Location: jenis_sampah.php?status=success");
                exit();
            } elseif ($action == 'update') {
                $id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE jenis_sampah SET nama_sampah = ?, harga_perkg = ?, kategori = ? WHERE id_sampah = ?");
                $stmt->execute([$jenis, $harga, $kategori, $id]);
                header("Location: jenis_sampah.php?status=updated");
                exit();
            }
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Ambil data terbaru (KOREKSI: Menggunakan id_sampah)
try {
    $stmt = $pdo->query("SELECT * FROM jenis_sampah ORDER BY id_sampah DESC");
    $jenis_sampah = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jenis Sampah - Manyar21</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        background-color: #f4f7f6; 
        font-family: 'Inter', 'Segoe UI', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

    /* 2. Sidebar - Penyesuaian Jarak agar tidak mepet (Identik Nasabah) */
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

    main, .main-content { 
        margin-left: 240px; 
        padding: 40px;
        width: calc(100% - 240px);
        min-height: 100vh;
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

    /* --- Kode lainnya (nav-link, card, btn-custom, dll) tetap sama --- */
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
    <div class="sidebar">
        <h5 class="mb-4"><i class="fas fa-recycle text-primary"></i> Admin Panel</h5>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="nasabah.php"><i class="fas fa-users"></i> Nasabah</a></li>
            <li class="nav-item"><a class="nav-link active" href="jenis_sampah.php"><i class="fas fa-recycle"></i> Jenis Sampah</a></li>
            <li class="nav-item"><a class="nav-link" href="transaksi.php"><i class="fas fa-history"></i> Transaksi</a></li>
            <li class="nav-item mb-2"><a class="nav-link" href="setor_sampah.php"><i class="fas fa-plus-circle"></i> Setor Sampah</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <main>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manajemen Jenis Sampah</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSampahModal">
                <i class="fas fa-plus"></i> Tambah Baru
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType; ?> alert-dismissible fade show">
                <?= $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jenis Sampah</th>
                                <th>Kategori</th>
                                <th>Harga/Kg</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jenis_sampah as $sampah): ?>
                            <tr>
                                <td><?= $sampah['id_sampah']; ?></td>
                                <td><strong><?= htmlspecialchars($sampah['nama_sampah']); ?></strong></td>
                                <td><span class="badge bg-info text-dark"><?= $sampah['kategori']; ?></span></td>
                                <td>Rp <?= number_format($sampah['harga_perkg'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-warning" 
                                        onclick="editSampah(<?= $sampah['id_sampah']; ?>, '<?= htmlspecialchars($sampah['nama_sampah'], ENT_QUOTES); ?>', <?= $sampah['harga_perkg']; ?>, '<?= $sampah['kategori']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                        onclick="prepareDelete(<?= $sampah['id_sampah']; ?>, '<?= htmlspecialchars($sampah['nama_sampah'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="addSampahModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Jenis Sampah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama Sampah</label><input type="text" name="jenis" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Harga/Kg</label><input type="number" name="harga_per_kg" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select" required>
                                <option value="Organik">Organik</option>
                                <option value="Anorganik">Anorganik</option>
                                <option value="B3">B3</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSampahModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id"> 
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Jenis Sampah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama Sampah</label><input type="text" name="jenis" id="edit_jenis" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Harga/Kg</label><input type="number" name="harga_per_kg" id="edit_harga" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" id="edit_kategori" class="form-select" required>
                                <option value="Organik">Organik</option>
                                <option value="Anorganik">Anorganik</option>
                                <option value="B3">B3</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header"><h5 class="modal-title">Hapus Sampah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Yakin ingin menghapus <strong id="delete_nama"></strong>?</p>
                        <small class="text-danger">*Data setoran terkait juga akan terhapus.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="delete_jenis_sampah" class="btn btn-danger">Hapus Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editSampah(id, nama, harga, kategori) {
            $('#edit_id').val(id);
            $('#edit_jenis').val(nama);
            $('#edit_harga').val(harga);
            $('#edit_kategori').val(kategori);
            new bootstrap.Modal(document.getElementById('editSampahModal')).show();
        }

        function prepareDelete(id, nama) {
            $('#delete_id').val(id);
            $('#delete_nama').text(nama);
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>