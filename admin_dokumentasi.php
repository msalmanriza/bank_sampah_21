<?php
require_once '../config.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Dokumentasi - Admin Bank Sampah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Poppins', sans-serif; }
        .admin-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .header-section { background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 30px; border-radius: 15px 15px 0 0; }
        .btn-save { background: #28a745; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; transition: 0.3s; }
        .btn-save:hover { background: #218838; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); }
        .image-upload-box { border: 2px dashed #ddd; padding: 20px; border-radius: 10px; background: #fff; text-align: center; }
    </style>
</head>
<body>

<div class="container py-5">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8"> 
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <a href="dashboard.php" class="text-decoration-none text-success fw-bold">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                </a>
            </div>

            <div class="card admin-card">
                <div class="header-section">
                    <h3 class="mb-0"><i class="fas fa-camera-retro me-2"></i> Post Dokumentasi Baru</h3>
                    <p class="small mb-0 opacity-75">Upload sekaligus maksimal 5 foto untuk satu kegiatan</p>
                </div>
                
                <div class="card-body p-4">
                    <form action="admin_proses_tambah.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Judul Kegiatan</label>
                            <input type="text" name="judul" class="form-control form-control-lg border-0 bg-light" placeholder="Contoh: Penimbangan Rutin RW 05" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-success"><i class="fas fa-images me-2"></i> Pilih Foto (Pilih 1 - 5 Foto)</label>
                            <div class="image-upload-box">
                                <input type="file" name="gambar[]" id="file-input" class="form-control" multiple accept="image/*" required>
                                <div id="preview-text" class="mt-3 small text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Foto pertama otomatis jadi <b>Foto Utama</b> di beranda.
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Deskripsi Kegiatan</label>
                            <textarea name="deskripsi" class="form-control border-0 bg-light" rows="5" placeholder="Tuliskan detail acara..." required></textarea>
                        </div>

                        <div class="text-end border-top pt-4">
                            <button type="reset" class="btn btn-outline-secondary px-4 rounded-pill me-2">Reset</button>
                            <button type="submit" class="btn btn-save text-white px-5">
                                <i class="fas fa-cloud-upload-alt me-2"></i> Simpan Dokumentasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('file-input').addEventListener('change', function() {
    if (this.files.length > 5) {
        alert("Waduh, maksimal cuma boleh 5 foto ya!");
        this.value = ""; 
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>