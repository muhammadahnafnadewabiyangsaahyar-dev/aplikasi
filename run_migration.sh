#!/bin/bash

echo "========================================="
echo "MIGRATION: Fix Komponen Gaji Default Values"
echo "========================================="
echo ""

# Jalankan migration
/Applications/XAMPP/xamppfiles/bin/mysql -u root << 'EOSQL'
USE aplikasi;

-- Update kolom komponen gaji dengan default value 0
ALTER TABLE komponen_gaji 
MODIFY COLUMN gaji_pokok DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN tunjangan_transport DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN tunjangan_makan DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN overwork DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN tunjangan_jabatan DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN bonus_kehadiran DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN bonus_marketing DECIMAL(10,2) NOT NULL DEFAULT 0,
MODIFY COLUMN insentif_omset DECIMAL(10,2) NOT NULL DEFAULT 0;

-- Update existing NULL values menjadi 0
UPDATE komponen_gaji SET gaji_pokok = 0 WHERE gaji_pokok IS NULL;
UPDATE komponen_gaji SET tunjangan_transport = 0 WHERE tunjangan_transport IS NULL;
UPDATE komponen_gaji SET tunjangan_makan = 0 WHERE tunjangan_makan IS NULL;
UPDATE komponen_gaji SET overwork = 0 WHERE overwork IS NULL;
UPDATE komponen_gaji SET tunjangan_jabatan = 0 WHERE tunjangan_jabatan IS NULL;
UPDATE komponen_gaji SET bonus_kehadiran = 0 WHERE bonus_kehadiran IS NULL;
UPDATE komponen_gaji SET bonus_marketing = 0 WHERE bonus_marketing IS NULL;
UPDATE komponen_gaji SET insentif_omset = 0 WHERE insentif_omset IS NULL;

-- Verifikasi
SELECT 'Migration Complete!' as status;
SELECT COUNT(*) as total_records FROM komponen_gaji;
EOSQL

echo ""
echo "========================================="
echo "Migration selesai!"
echo "========================================="
