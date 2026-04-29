<?php
session_start();

// 1. PERBAIKAN: Sesuaikan dengan session id_users dan role admin
if (!isset($_SESSION['id_users']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Handle CRUD operations
$message = '';
$messageType = '';

// Menangkap pesan sukses dari redirect
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'deleted') {
        $message = "Nasabah berhasil dihapus!";
        $messageType = "success";
    } elseif ($_GET['status'] == 'updated') {
        $message = "Data nasabah berhasil diperbarui!";
        $messageType = "success";
    }
}


// Pastikan baris ini diletakkan setelah inisialisasi $message dan $messageType
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    try { 
        // 1. LOGIC TAMBAH NASABAH
        if (isset($_POST['add_nasabah'])) {
            $nama = $_POST['nama'];
            $username = $_POST['username'];
            $email = $_POST['email'];
            $no_hp = $_POST['no_hp'];
            $alamat = $_POST['alamat'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $role = 'nasabah';
            $saldo = 0;

            if (empty($nama) || empty($email) || empty($username) || empty($password)) {
                throw new Exception('Data utama nasabah harus diisi!');
            }

            $sql = "INSERT INTO users (nama, username, email, no_hp, alamat, password, role, saldo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $username, $email, $no_hp, $alamat, $password, $role, $saldo]);

            header("Location: nasabah.php?status=success");
            exit();
        } 

        // 2. LOGIC UPDATE NASABAH
        elseif (isset($_POST['update_nasabah'])) {
            $id = $_POST['id'];
            $nama = trim($_POST['nama']);
            $email = trim($_POST['email']);
            $no_hp = trim($_POST['no_hp']);
            $alamat = trim($_POST['alamat']);

            $sql = "UPDATE users SET nama = ?, email = ?, no_hp = ?, alamat = ? 
                    WHERE id_users = ? AND role = 'nasabah'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $email, $no_hp, $alamat, $id]);

            header("Location: nasabah.php?status=updated");
            exit();
        }

        // 3. LOGIC DELETE NASABAH (Auto-Cleanup Mode)
        elseif (isset($_POST['delete_nasabah'])) {
            $id = $_POST['id'];

            // Mulai transaksi database agar aman
            $pdo->beginTransaction();

            // Hapus paksa di tabel relasi agar nasabah bisa dihapus
            $pdo->prepare("DELETE FROM setor_sampah WHERE id_users = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM transaksi WHERE id_users = ?")->execute([$id]);

            // Hapus data utama nasabah
            $stmt = $pdo->prepare("DELETE FROM users WHERE id_users = ? AND role = 'nasabah'");
            $stmt->execute([$id]);

            $pdo->commit();

            header("Location: nasabah.php?status=deleted");
            exit();
        }

    } catch (Exception $e) {
        // --- PENYELAMAT: Bagian Catch untuk menangkap Error ---
        $message = $e->getMessage();
        $messageType = 'danger';
        
        // Jika sedang proses delete tapi gagal, batalkan perubahan database
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
} // <-- Penutup block POST

try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'nasabah' ORDER BY nama ASC");
    $nasabah = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Nasabah - Bank Sampah Manyar21</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    /* Base Background */
    body {
        background-color: #f4f7f6; 
        font-family: 'Inter', 'Segoe UI', sans-serif;
        margin: 0;
        overflow-x: hidden;
    }

    /* Sidebar Modern Minimalis */
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

    .btn-primary {
    background: #667eea !important;
    border: none !important;
    }

    .btn-primary:hover {
    background: #667eea !important;
    box-shadow: 0 4px 12px rgba(118, 75, 162, 0.3);
    }

    /* Gaya Link Navigasi */
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

    /* Card Box agar senada dengan Dashboard */
    .card { 
        border: 1px solid #edf2f7 !important; 
        border-radius: 15px; 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; 
        background: #ffffff;
    }

    /* Tombol Custom */
    .btn-custom { 
        border-radius: 10px; 
        padding: 8px 16px; 
        font-weight: 500; 
    }

    /* Header Tabel Professional */
    .table th { 
        background: #f8f9fa !important; /* Abu-abu sangat muda */
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
                    <h2>Manajemen Nasabah</h2>
                    <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#addNasabahModal">
                        <i class="fas fa-plus"></i> Tambah Nasabah
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>No. HP</th>
                                        <th>Alamat</th>
                                        <th>Email</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($nasabah as $n): ?>
                                        <tr>
                                            <td><?php echo $n['id_users']; ?></td>
                                            <td><?php echo htmlspecialchars($n['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($n['no_hp']); ?></td>
                                            <td><?php echo htmlspecialchars($n['alamat']); ?></td>
                                            <td><?php echo htmlspecialchars($n['email']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning btn-custom me-1" 
                                                    onclick="editNasabah(
                                                        '<?php echo $n['id_users']; ?>', 
                                                        '<?php echo htmlspecialchars($n['nama']); ?>', 
                                                        '<?php echo htmlspecialchars($n['email']); ?>',
                                                        '<?php echo htmlspecialchars($n['no_hp']); ?>', 
                                                        '<?php echo htmlspecialchars($n['alamat']); ?>'
                                                    )">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-custom" 
                                                        onclick="deleteNasabah(<?php echo $n['id_users']; ?>, '<?php echo htmlspecialchars($n['nama']); ?>')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editNasabah(id, nama, email, no_hp, alamat) {
        // Mengisi value ke modal edit
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_no_hp').value = no_hp;
        document.getElementById('edit_alamat').value = alamat;
        
        // Memunculkan modal edit
        var editModal = new bootstrap.Modal(document.getElementById('editNasabahModal'));
        editModal.show();
    }

    function deleteNasabah(id, nama) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_name').textContent = nama;
        
        // Memunculkan modal delete
        var delModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        delModal.show();
    }
    </script>
    <div class="modal fade" id="addNasabahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Nasabah Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="nasabah.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_nasabah" class="btn btn-primary">Simpan Nasabah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editNasabahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Nasabah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="nasabah.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" id="edit_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" id="edit_no_hp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" id="edit_alamat" class="form-control" rows="3" required></textarea>
                    </div>
                    <p class="text-muted small">*Password tidak dapat diubah di sini demi keamanan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_nasabah" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Apakah Anda yakin ingin menghapus nasabah <strong id="delete_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="delete_nasabah" class="btn btn-danger">Hapus Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>