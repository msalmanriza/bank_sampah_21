-- Database Setup for Bank Sampah Manyar21 Admin Panel
-- Run this SQL script to create the database and tables

-- Create database
CREATE DATABASE IF NOT EXISTS bank_sampah;
USE bank_sampah;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'nasabah') DEFAULT 'nasabah',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create sampah table
CREATE TABLE IF NOT EXISTS sampah (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis VARCHAR(100) UNIQUE NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create transaksi table
CREATE TABLE IF NOT EXISTS transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jenis_sampah INT NOT NULL,
    berat DECIMAL(8,2) NOT NULL,
    poin INT NOT NULL,
    status ENUM('pending', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_sampah) REFERENCES sampah(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin12345)
INSERT IGNORE INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@banksampah.com', 'admin12345', 'admin');

-- Insert sample jenis sampah
INSERT IGNORE INTO sampah (jenis, harga_per_kg) VALUES
('Plastik', 2000.00),
('Kertas', 1500.00),
('Logam', 4000.00),
('Kaca', 2000.00),
('Kardus', 1800.00),
('Botol Plastik', 2500.00);

-- Insert sample nasabah
INSERT IGNORE INTO users (nama, email, password, role) VALUES
('Ahmad Rahman', 'ahmad@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nasabah'),
('Siti Nurhaliza', 'siti@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nasabah'),
('Budi Santoso', 'budi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'nasabah');

-- Insert sample transaksi
INSERT IGNORE INTO transaksi (user_id, jenis_sampah, berat, poin, status) VALUES
(2, 1, 5.2, 10, 'selesai'),
(2, 2, 3.8, 6, 'selesai'),
(3, 3, 2.1, 8, 'pending'),
(4, 4, 4.5, 9, 'selesai'),
(2, 1, 6.0, 12, 'pending');

-- Create indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_transaksi_user_id ON transaksi(user_id);
CREATE INDEX idx_transaksi_status ON transaksi(status);
CREATE INDEX idx_transaksi_tanggal ON transaksi(tanggal);
