<?php
// admin_proses_tambah.php
require_once '../config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal = date('Y-m-d H:i:s');
    $target_dir = "../uploads/kegiatan/"; 

    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

    $db_cols = ['gambar', 'gambar2', 'gambar3', 'gambar4', 'gambar5'];
    $file_names = array_fill_keys($db_cols, NULL);

    // Proses Upload Multi-file
    if (!empty($_FILES['gambar']['name'][0])) {
        $total_files = min(count($_FILES['gambar']['name']), 5);
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gambar']['error'][$i] === 0) {
                $original_name = preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['gambar']['name'][$i]);
                $new_name = time() . "_" . rand(100, 999) . "_" . $original_name;
                
                if (move_uploaded_file($_FILES['gambar']['tmp_name'][$i], $target_dir . $new_name)) {
                    $file_names[$db_cols[$i]] = $new_name;
                }
            }
        }
    }

    try {
        $sql = "INSERT INTO dokumentasi (judul, deskripsi, gambar, tanggal_upload, gambar2, gambar3, gambar4, gambar5) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $judul, 
            $deskripsi, 
            $file_names['gambar'], 
            $tanggal, 
            $file_names['gambar2'], 
            $file_names['gambar3'], 
            $file_names['gambar4'], 
            $file_names['gambar5']
        ]);

        // --- SOLUSI UTAMA: REDIRECT ---
        // Dengan redirect, browser tidak akan menyimpan data POST lagi.
        // Jika di-refresh, browser hanya me-refresh halaman dokumentasi.php (GET), bukan proses tambahnya.
        header("Location: dokumentasi.php?msg=Konten berhasil dipublikasikan!");
        exit(); // Wajib exit setelah header agar kode di bawahnya tidak dieksekusi

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
} else {
    // Jika ada yang mencoba akses file ini langsung via URL (GET), tendang balik ke form
    header("Location: dokumentasi.php");
    exit();
}