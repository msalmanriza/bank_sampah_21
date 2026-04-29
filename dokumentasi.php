<?php
session_start();
if (!isset($_SESSION['id_users']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';
date_default_timezone_set('Asia/Jakarta');

// --- LOGIC DELETE (FITUR HAPUS) ---
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    
    $stmt_get = $pdo->prepare("SELECT gambar, gambar2, gambar3, gambar4, gambar5 FROM dokumentasi WHERE id_dokumentasi = ?");
    $stmt_get->execute([$id_hapus]);
    $data_kegiatan = $stmt_get->fetch();
    
    if ($data_kegiatan) {
        $cols = ['gambar', 'gambar2', 'gambar3', 'gambar4', 'gambar5'];
        foreach ($cols as $col) {
            if (!empty($data_kegiatan[$col])) {
                $filePath = "uploads/kegiatan/" . $data_kegiatan[$col];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        $stmt_del = $pdo->prepare("DELETE FROM dokumentasi WHERE id_dokumentasi = ?");
        $stmt_del->execute([$id_hapus]);
        
        // REDIRECT SETELAH HAPUS
        header("Location: dokumentasi.php?status=deleted");
        exit();
    }
}

// --- LOGIC UPLOAD KEGIATAN ---
if (isset($_POST['upload_kegiatan'])) {
    $judul = htmlspecialchars($_POST['judul']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $targetDir = "uploads/kegiatan/";
    
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
    
    $db_cols = ['gambar', 'gambar2', 'gambar3', 'gambar4', 'gambar5'];
    $file_names = array_fill_keys($db_cols, NULL);

    if (isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'][0])) {
        $total_files = min(count($_FILES['gambar']['name']), 5);

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gambar']['error'][$i] == 0) {
                $ext = strtolower(pathinfo($_FILES["gambar"]["name"][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $fileName = time() . '_' . $i . '_' . basename($_FILES["gambar"]["name"][$i]);
                    $targetFilePath = $targetDir . $fileName;

                    if (move_uploaded_file($_FILES["gambar"]["tmp_name"][$i], $targetFilePath)) {
                        $file_names[$db_cols[$i]] = $fileName;
                    }
                }
            }
        }

        // Simpan ke database
        $stmt = $pdo->prepare("INSERT INTO dokumentasi (judul, deskripsi, gambar, gambar2, gambar3, gambar4, gambar5, tanggal_upload) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $judul, 
            $deskripsi, 
            $file_names['gambar'], 
            $file_names['gambar2'], 
            $file_names['gambar3'], 
            $file_names['gambar4'], 
            $file_names['gambar5']
        ]);
        
        // --- FIX UTAMA: REDIRECT AGAR TIDAK DOUBLE UPLOAD ---
        header("Location: dokumentasi.php?status=success");
        exit();
    }
}

$listKegiatan = $pdo->query("SELECT * FROM dokumentasi ORDER BY tanggal_upload DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Dokumentasi - Bank Sampah Manyar21</title>
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

/* Update juga warna tombol Publish agar senada dengan sidebar */
.btn-primary {
    background: #764ba2 !important;
    border: none !important;
}

.btn-primary:hover {
    background: #667eea !important;
    box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
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
    border-right: 4px solid #ffffff;    /* Opsional: Tambah garis penanda di kanan agar lebih keren */
    }
        
    main, .main-content { 
        margin-left: 240px; 
        padding: 40px;
        width: calc(100% - 240px);
        min-height: 100vh;
    }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .table img { object-fit: cover; height: 50px; width: 60px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h5 class="mb-4"><i class="fas fa-recycle"></i> Admin Panel</h5>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="nasabah.php"><i class="fas fa-users me-2"></i> Nasabah</a></li>
            <li class="nav-item"><a class="nav-link" href="transaksi.php"><i class="fas fa-history me-2"></i> Transaksi</a></li>
            <li class="nav-item"><a class="nav-link active" href="dokumentasi.php"><i class="fas fa-camera me-2"></i> Dokumentasi</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>Manajemen Konten (CMS)</h2>
        <p class="text-muted">Upload sekaligus 5 foto untuk satu kegiatan.</p>
        
        <?php 
            if(isset($_GET['status']) && $_GET['status'] == 'success') {
                echo "<div class='alert alert-success shadow-sm'>Konten berhasil dipublikasikan!</div>";
            }
            if(isset($_GET['status']) && $_GET['status'] == 'deleted') {
                echo "<div class='alert alert-warning shadow-sm'>Konten berhasil dihapus!</div>";
            }
        ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card p-4">
                    <h5 class="mb-3">Tambah Kegiatan</h5>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Judul</label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Upload Foto (Maks 5)</label>
                            <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple required>
                            <small class="text-muted">Foto urutan pertama akan jadi cover di Beranda</small>
                        </div>
                        <button type="submit" name="upload_kegiatan" class="btn btn-primary w-100" style="background: #28a745; border: none;">
                            <i class="fas fa-upload me-1"></i> Publish
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card p-4">
                    <h5 class="mb-3">Daftar Kegiatan Terpublikasi</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Cover</th>
                                    <th>Judul</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($listKegiatan) > 0): ?>
                                    <?php foreach($listKegiatan as $kg): ?>
                                    <tr>
                                        <td>
                                            <img src="uploads/kegiatan/<?php echo $kg['gambar']; ?>" class="rounded shadow-sm">
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($kg['judul']); ?></strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($kg['tanggal_upload'])); ?></td>
                                        <td>
                                            <a href="?hapus=<?php echo $kg['id_dokumentasi']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Apakah Anda yakin? Semua foto terkait kegiatan ini akan dihapus.')">
                                               <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada data kegiatan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>