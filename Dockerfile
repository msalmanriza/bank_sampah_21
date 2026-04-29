# Menggunakan image PHP resmi dengan Apache
FROM php:8.1-apache

# Instal ekstensi yang diperlukan untuk koneksi MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Mengaktifkan modul rewrite Apache (penting untuk routing PHP)
RUN a2enmod rewrite

# Salin semua file project Anda ke dalam folder server di kontainer
COPY . /var/www/html/

# Atur izin akses agar server bisa membaca file project
RUN chown -R www-data:www-data /var/www/html/

# Port standar untuk web
EXPOSE 80