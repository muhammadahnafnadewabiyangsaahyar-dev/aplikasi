-- Add decline_reason column to shift_assignments table
ALTER TABLE shift_assignments 
ADD COLUMN decline_reason ENUM('sakit', 'izin', 'reschedule') NULL DEFAULT NULL 
AFTER catatan_pegawai;

-- Add comment for documentation
ALTER TABLE shift_assignments 
MODIFY COLUMN decline_reason ENUM('sakit', 'izin', 'reschedule') NULL DEFAULT NULL 
COMMENT 'Alasan penolakan: sakit, izin, atau reschedule';

-- Show table structure
DESCRIBE shift_assignments;
