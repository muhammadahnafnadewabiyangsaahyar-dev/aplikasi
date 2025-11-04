# 🧹 CLEANUP PLAN

## FILES TO DELETE

### 1. OLD SQL BACKUPS (Keep only latest)
- backup_20251104.sql (KEEP - Latest)
- backup_before_absen_fix_20251103_011311.sql (DELETE)
- backup_pre_migration_20251104_002350.sql (DELETE)
- backup_pre_migration_20251104_003659.sql (DELETE)
- backup_pre_migration_20251104_003724.sql (DELETE)
- backup_pre_migration_final_20251104_003802.sql (DELETE)
- backup_pre_migration_manual_20251104_002640.sql (DELETE)
- aplikasi_backup_20251102_234405.sql (DELETE)

### 2. TEST/TEMPORARY SQL FILES
- test_lupa_absen_pulang.sql (DELETE - test file)
- cleanup_duplicate_absen.sql (DELETE - already executed)
- cleanup_tes_outlet.sql (DELETE - already executed)
- INSERT_DUMMY_USERS.sql (DELETE - replaced by dummy_data_complete.sql)
- INSERT_SAMPLE_ASSIGNMENTS.sql (DELETE - replaced by dummy_data_complete.sql)

### 3. REDUNDANT MIGRATION FILES
- migration_fix_komponen_gaji_default.sql (DELETE - superset in main migration)
- migration_komponen_gaji_default.sql (DELETE - duplicate)
- update_komponen_gaji_nullable.sql (DELETE - already applied)
- update_komponen_gaji_allow_null.sql (DELETE - duplicate)
- migration_add_salary_to_whitelist.sql (DELETE - already applied)
- migration_add_status_kehadiran.sql (DELETE - already applied)
- migration_fix_admin_data.sql (DELETE - already applied)
- update_absen_photo_paths.sql (DELETE - already applied)
- fix_timezone_wita.sql (DELETE - already applied)
- fix_absen_di_luar_shift.sql (DELETE - already applied)
- migration_absen_fixes.sql (DELETE - already applied)
- migration_fix_keterlambatan.sql (DELETE - replaced by complete version)
- pre_migration_patch.sql (DELETE - already applied)
- migration_shift_system.sql (DELETE - replaced by enhanced version)

### 4. KEEP (IMPORTANT FILES)
- aplikasi.sql (KEEP - Main database)
- aplikasi_cleaned.sql (KEEP - Clean version)
- migration_shift_enhancement.sql (KEEP - Main migration)
- migration_keterlambatan_complete.sql (KEEP - Complete keterlambatan)
- database_schema_shift_system.sql (KEEP - Schema reference)
- dummy_data_complete.sql (KEEP - Dummy data)
- populate_salary_data.sql (KEEP - Salary data)

### 5. AUTO BACKUPS (Keep last 5 only)
- backups/aplikasi_auto_*.sql (DELETE old ones, keep last 5)

## FILES TO MERGE/CONSOLIDATE

### Documentation Files
Merge into one MASTER_GUIDE.md:
- QUICK_START.md
- IMPLEMENTATION_GUIDE.md
- PRE_MIGRATION_PATCH_README.md
- MIGRATION_SUCCESS_REPORT.md
- MIGRATION_ANALYSIS.md
- README.md (keep separate but simplify)

### Kalender Files
Keep in KALENDER/:
- kalender.html
- script_hybrid.js
- api_kalender.php
- connect_mysqli.php
- HYBRID_CALENDAR_COMPLETE.md
- verify_features.html

Delete:
- scriptkalender.js (backup only, not used)
- script_database.js (replaced by hybrid)

## TOTAL CLEANUP
- Delete: ~50 SQL files
- Delete: ~20 MD files (after merge)
- Keep: ~15 essential files
- Result: 70% reduction in file count
