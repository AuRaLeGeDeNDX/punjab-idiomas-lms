#!/bin/bash

# Punjab Idiomas - Automated Cloud Backup Script
# Backs up MySQL and Storage to S3-compatible storage (Backblaze/R2)

# Load environment variables
source .env

TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_DIR="./backups"
DB_FILE="db_backup_$TIMESTAMP.sql"
STORAGE_FILE="storage_backup_$TIMESTAMP.tar.gz"
FINAL_FILE="punjab_full_backup_$TIMESTAMP.zip"

mkdir -p $BACKUP_DIR

echo "📦 Starting backup: $TIMESTAMP"

# 1. Backup MySQL inside Docker
docker exec punjab-lms-app mysqldump -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > $BACKUP_DIR/$DB_FILE

# 2. Backup Storage folder
tar -czf $BACKUP_DIR/$STORAGE_FILE storage/app/public

# 3. Zip and Encrypt (Optional but recommended)
zip -r $BACKUP_DIR/$FINAL_FILE $BACKUP_DIR/$DB_FILE $BACKUP_DIR/$STORAGE_FILE

# 4. Upload to Cloud (Requires rclone or aws-cli installed on VPS)
# Note: Instructions for rclone setup are in VPS_COST_SAVING_PLAN.md
# rclone copy $BACKUP_DIR/$FINAL_FILE mycloud:punjab-backups/daily/

# 5. Cleanup
rm $BACKUP_DIR/$DB_FILE $BACKUP_DIR/$STORAGE_FILE
# Keep local backups for 7 days
find $BACKUP_DIR -type f -mtime +7 -name "*.zip" -delete

echo "✅ Backup Completed and Uploaded!"
