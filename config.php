<?php
// Mencegah akses langsung ke file config
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Direct access not allowed.");
}

$host     = 'localhost';
$dbname   = 'bank_sampah_21';
$username = 'root';
$password = '';
$port     = '3307'; 

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Set timezone ke WIB (Asia/Jakarta)
    $pdo->exec("SET time_zone = '+07:00'");
    
} catch(PDOException $e) {
    // Pada tahap produksi, sebaiknya pesan error tidak sedetail ini demi keamanan
    die("Koneksi database gagal. Silakan hubungi admin."); 
}
?>