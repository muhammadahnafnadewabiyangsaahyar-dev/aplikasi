#!/bin/zsh
# Jalankan backup database
./backup_db.sh

# Cek koneksi database via PHP
php -r "require 'connect.php'; echo $pdo ? 'Koneksi OK\n' : 'Koneksi Gagal\n';"

# (Opsional) Jalankan test lain, misal: curl ke endpoint aplikasi
curl -s -o /dev/null -w '%{http_code}\n' http://localhost/aplikasi/index.php