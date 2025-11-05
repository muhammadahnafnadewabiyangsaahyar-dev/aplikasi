-- Tabel untuk menyimpan jadwal shift karyawan
CREATE TABLE IF NOT EXISTS `shift_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `cabang_id` int(11) NOT NULL,
  `tanggal_shift` date NOT NULL,
  `status_konfirmasi` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `cabang_id` (`cabang_id`),
  KEY `tanggal_shift` (`tanggal_shift`),
  UNIQUE KEY `unique_user_date` (`user_id`, `tanggal_shift`),
  CONSTRAINT `fk_shift_user` FOREIGN KEY (`user_id`) REFERENCES `register` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shift_cabang` FOREIGN KEY (`cabang_id`) REFERENCES `cabang` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shift_creator` FOREIGN KEY (`created_by`) REFERENCES `register` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index untuk performa query
CREATE INDEX idx_user_date ON shift_assignments(user_id, tanggal_shift);
CREATE INDEX idx_cabang_date ON shift_assignments(cabang_id, tanggal_shift);
