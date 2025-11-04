#!/bin/zsh
DB_USER="root"
DB_PASS=""
DB_NAME="aplikasi"
SQL_FILE="/Applications/XAMPP/xamppfiles/htdocs/aplikasi/aplikasi.sql"
LOG_FILE="/Applications/XAMPP/xamppfiles/htdocs/aplikasi/import_auto.log"

if [ -f "$SQL_FILE" ]; then
  if [ -z "$DB_PASS" ]; then
    /Applications/XAMPP/xamppfiles/bin/mysql -u"$DB_USER" "$DB_NAME" < "$SQL_FILE" 2>> "$LOG_FILE"
  else
    /Applications/XAMPP/xamppfiles/bin/mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE" 2>> "$LOG_FILE"
  fi
  if [ $? -eq 0 ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') Import sukses" >> "$LOG_FILE"
  else
    echo "$(date '+%Y-%m-%d %H:%M:%S') Import gagal" >> "$LOG_FILE"
  fi
else
  echo "$(date '+%Y-%m-%d %H:%M:%S') File SQL tidak ditemukan" >> "$LOG_FILE"
fi
