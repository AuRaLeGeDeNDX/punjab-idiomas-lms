# Complete Assignment System - Deployment Guide

## Overview

This guide provides comprehensive instructions for deploying the Complete Assignment System to production. It includes pre-deployment checks, deployment steps, post-deployment verification, rollback procedures, and troubleshooting.

**Version**: 1.0  
**Last Updated**: February 2, 2026  
**Target Environment**: Production

---

## Table of Contents

1. [Pre-Deployment Checklist](#pre-deployment-checklist)
2. [System Requirements](#system-requirements)
3. [Deployment Steps](#deployment-steps)
4. [Database Migration](#database-migration)
5. [Configuration](#configuration)
6. [Post-Deployment Verification](#post-deployment-verification)
7. [Rollback Procedures](#rollback-procedures)
8. [Monitoring & Maintenance](#monitoring--maintenance)
9. [Troubleshooting](#troubleshooting)
10. [Security Considerations](#security-considerations)

---

## Pre-Deployment Checklist

### Code Readiness

- [ ] All code merged to main/production branch
- [ ] Code review completed and approved
- [ ] All tests passing (unit, feature, integration)
- [ ] No critical or high-priority bugs
- [ ] Documentation updated
- [ ] Changelog prepared

### Database Readiness

- [ ] All migrations tested in staging
- [ ] Migration rollback scripts tested
- [ ] Database backup completed
- [ ] Migration order verified
- [ ] Data migration scripts tested
- [ ] Indexes optimized

### Infrastructure Readiness

- [ ] Server resources adequate (CPU, RAM, disk)
- [ ] PHP version compatible (8.1+)
- [ ] MySQL version compatible (8.0+)
- [ ] Redis installed and configured
- [ ] File storage configured (local or S3)
- [ ] SSL certificates valid
- [ ] Firewall rules configured

### Team Readiness

- [ ] Deployment team identified
- [ ] Deployment window scheduled
- [ ] Stakeholders notified
- [ ] Support team briefed
- [ ] Rollback plan reviewed
- [ ] Communication plan ready

### Backup & Recovery

- [ ] Full database backup completed
- [ ] File storage backup completed
- [ ] Configuration files backed up
- [ ] Backup restoration tested
- [ ] Recovery time objective (RTO) defined
- [ ] Recovery point objective (RPO) defined

---

## System Requirements

### Server Requirements

**Minimum**:
- CPU: 2 cores
- RAM: 4 GB
- Disk: 50 GB SSD
- Network: 100 Mbps

**Recommended**:
- CPU: 4+ cores
- RAM: 8+ GB
- Disk: 100+ GB SSD
- Network: 1 Gbps

### Software Requirements

**Required**:
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Redis 6.0 or higher
- Composer 2.x
- Node.js 18.x or higher
- NPM 9.x or higher

**PHP Extensions**:
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick
- Redis

### Browser Support

**Supported Browsers**:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

---

## Deployment Steps

### Step 1: Prepare Environment

```bash
# 1. Navigate to project directory
cd /var/www/html/lms

# 2. Enable maintenance mode
php artisan down --message="System upgrade in progress" --retry=60

# 3. Pull latest code
git fetch origin
git checkout production
git pull origin production

# 4. Verify correct branch and commit
git log -1
```

### Step 2: Update Dependencies

```bash
# 1. Update Composer dependencies
composer install --no-dev --optimize-autoloader

# 2. Update NPM dependencies
npm ci --production

# 3. Compile assets
npm run build

# 4. Clear old compiled assets
rm -rf public/build/old-*
```

### Step 3: Database Backup

```bash
# 1. Create backup directory
mkdir -p storage/backups/$(date +%Y%m%d)

# 2. Backup database
mysqldump -u [username] -p[password] [database] > storage/backups/$(date +%Y%m%d)/database_backup.sql

# 3. Backup .env file
cp .env storage/backups/$(date +%Y%m%d)/.env.backup

# 4. Verify backup
ls -lh storage/backups/$(date +%Y%m%d)/
```

### Step 4: Run Migrations

```bash
# 1. Check migration status
php artisan migrate:status

# 2. Run migrations (dry run first if available)
php artisan migrate --pretend

# 3. Run actual migrations
php artisan migrate --force

# 4. Verify migrations
php artisan migrate:status
```

### Step 5: Clear Caches

```bash
# 1. Clear application cache
php artisan cache:clear

# 2. Clear config cache
php artisan config:clear

# 3. Clear route cache
php artisan route:clear

# 4. Clear view cache
php artisan view:clear

# 5. Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: File Permissions

```bash
# 1. Set ownership
chown -R www-data:www-data storage bootstrap/cache

# 2. Set permissions
chmod -R 775 storage bootstrap/cache

# 3. Verify permissions
ls -la storage/
```

### Step 7: Queue Workers

```bash
# 1. Restart queue workers
php artisan queue:restart

# 2. Verify workers are running
ps aux | grep "queue:work"

# 3. Start workers if not running
php artisan queue:work --daemon &
```

### Step 8: Scheduler Setup

```bash
# 1. Verify cron job exists
crontab -l | grep schedule:run

# 2. Add cron job if missing
(crontab -l 2>/dev/null; echo "* * * * * cd /var/www/html/lms && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# 3. Test scheduler
php artisan schedule:run
```

### Step 9: Disable Maintenance Mode

```bash
# 1. Bring application back online
php artisan up

# 2. Verify site is accessible
curl -I https://yourdomain.com
```

---

## Database Migration

### Migration Files

The following migrations will be executed in order:

1. `2026_02_02_071330_create_submission_files_table.php`
2. `2026_02_02_071659_migrate_file_paths_to_submission_files.php`
3. `2026_02_02_071700_create_submission_versions_table.php`
4. `2026_02_02_071800_create_notification_preferences_table.php`
5. `2026_02_02_120000_create_assignment_templates_table.php`
6. `2026_02_02_120001_create_rubrics_tables.php`
7. `2026_02_02_120002_add_is_locked_to_grades_table.php`
8. `2026_02_02_120003_create_grade_overrides_table.php`
9. `2026_02_02_172031_add_scheduled_publish_at_to_assignments_table.php`

### Data Migration

**File Paths Migration**:
- Converts JSON `file_paths` to normalized `submission_files` records
- Preserves all file metadata
- Maintains referential integrity
- Estimated time: 1-5 minutes per 1000 submissions

**Monitoring Migration**:
```bash
# Watch migration progress
tail -f storage/logs/laravel.log | grep "Migration"

# Check submission_files count
mysql -u [user] -p[pass] -e "SELECT COUNT(*) FROM submission_files;" [database]
```

### Migration Verification

```bash
# 1. Verify all tables created
php artisan migrate:status

# 2. Check table structures
mysql -u [user] -p[pass] [database] -e "DESCRIBE submission_files;"
mysql -u [user] -p[pass] [database] -e "DESCRIBE submission_versions;"
mysql -u [user] -p[pass] [database] -e "DESCRIBE grade_overrides;"

# 3. Verify data migration
php artisan tinker
>>> \App\Models\Submission::has('files')->count()
>>> \App\Models\SubmissionFile::count()
```

---

## Configuration

### Environment Variables

Update `.env` file with production values:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_production
DB_USERNAME=lms_user
DB_PASSWORD=secure_password_here

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=redis_password_here
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=mail_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=local
# Or for S3:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your_key
# AWS_SECRET_ACCESS_KEY=your_secret
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your_bucket

# Assignment System
ASSIGNMENT_MAX_FILE_SIZE=10485760  # 10MB in bytes
ASSIGNMENT_MAX_TOTAL_SIZE=52428800  # 50MB in bytes
ASSIGNMENT_MAX_FILES=5
ASSIGNMENT_ALLOWED_EXTENSIONS=pdf,doc,docx,txt,jpg,jpeg,png,gif,zip,rar

# Analytics Cache
ANALYTICS_CACHE_TTL=300  # 5 minutes
STUDENT_DASHBOARD_CACHE_TTL=120  # 2 minutes
```

### File Storage Configuration

**Local Storage** (config/filesystems.php):
```php
'disks' => [
    'assignments' => [
        'driver' => 'local',
        'root' => storage_path('app/assignments'),
        'visibility' => 'private',
    ],
],
```

**S3 Storage** (config/filesystems.php):
```php
'disks' => [
    'assignments' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'visibility' => 'private',
    ],
],
```

### Queue Configuration

**config/queue.php**:
```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

### Cache Configuration

**config/cache.php**:
```php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],
```

---

## Post-Deployment Verification

### Automated Tests

```bash
# 1. Run feature tests
php artisan test --testsuite=Feature

# 2. Run integration tests
php artisan test --group=integration

# 3. Check for errors
tail -n 100 storage/logs/laravel.log
```

### Manual Verification

#### Assignment Creation
1. Log in as teacher
2. Navigate to course → module → subpage
3. Click "Create Assignment"
4. Fill in all fields
5. Publish assignment
6. Verify students can see it

#### File Upload
1. Log in as student
2. Open assignment
3. Upload test file (PDF, image, document)
4. Verify upload completes
5. Submit assignment
6. Verify teacher can download file

#### Grading
1. Log in as teacher
2. View student submission
3. Enter grade and feedback
4. Publish grade
5. Verify student receives notification
6. Verify student can see grade

#### Analytics
1. Log in as teacher
2. Navigate to Analytics Dashboard
3. Verify data displays correctly
4. Check charts render properly
5. Verify calculations are accurate

#### Scheduling
1. Create assignment
2. Schedule for 2 minutes in future
3. Wait for scheduled time
4. Verify auto-publication occurs
5. Check notifications sent

#### Bulk Operations
1. Select multiple submissions
2. Test bulk download (ZIP creation)
3. Test bulk export (CSV generation)
4. Test bulk reminders
5. Verify all operations complete

### Performance Verification

```bash
# 1. Check page load times
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com/dashboard

# 2. Check database query performance
php artisan telescope:prune
# Then monitor queries in Telescope

# 3. Check cache hit rates
redis-cli INFO stats | grep keyspace_hits

# 4. Check queue processing
php artisan queue:monitor
```

### Security Verification

```bash
# 1. Verify HTTPS is enforced
curl -I http://yourdomain.com
# Should redirect to HTTPS

# 2. Check file permissions
ls -la storage/
ls -la bootstrap/cache/

# 3. Verify .env is not accessible
curl https://yourdomain.com/.env
# Should return 404

# 4. Check security headers
curl -I https://yourdomain.com
# Look for X-Frame-Options, X-Content-Type-Options, etc.
```

---

## Rollback Procedures

### When to Rollback

Rollback if:
- Critical bugs discovered
- Data corruption detected
- Performance degradation severe
- Security vulnerability found
- User-facing errors widespread

### Rollback Steps

#### Step 1: Enable Maintenance Mode

```bash
php artisan down --message="Rolling back deployment" --retry=60
```

#### Step 2: Restore Code

```bash
# 1. Checkout previous version
git log --oneline -10  # Find previous commit
git checkout [previous-commit-hash]

# 2. Reinstall dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build
```

#### Step 3: Rollback Database

```bash
# 1. Identify migrations to rollback
php artisan migrate:status

# 2. Rollback migrations (one step at a time)
php artisan migrate:rollback --step=1

# 3. Or restore from backup
mysql -u [user] -p[pass] [database] < storage/backups/[date]/database_backup.sql
```

#### Step 4: Restore Configuration

```bash
# Restore .env file
cp storage/backups/[date]/.env.backup .env
```

#### Step 5: Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### Step 6: Disable Maintenance Mode

```bash
php artisan up
```

#### Step 7: Verify Rollback

```bash
# 1. Check application version
git log -1

# 2. Verify database state
php artisan migrate:status

# 3. Test critical functionality
curl -I https://yourdomain.com
```

### Post-Rollback Actions

1. **Notify Stakeholders**: Inform team and users of rollback
2. **Document Issues**: Record what went wrong
3. **Analyze Root Cause**: Investigate failure
4. **Plan Fix**: Determine corrective actions
5. **Schedule Redeployment**: Plan next deployment attempt

---

## Monitoring & Maintenance

### Application Monitoring

**Laravel Telescope** (Development/Staging):
```bash
# Install Telescope
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Log Monitoring**:
```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log

# Count errors by type
grep "ERROR" storage/logs/laravel.log | cut -d' ' -f4 | sort | uniq -c
```

### Database Monitoring

```sql
-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
FROM information_schema.TABLES
WHERE table_schema = "lms_production"
ORDER BY (data_length + index_length) DESC;

-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 10;

-- Check connection count
SHOW STATUS LIKE 'Threads_connected';
```

### Cache Monitoring

```bash
# Redis stats
redis-cli INFO stats

# Cache hit rate
redis-cli INFO stats | grep keyspace

# Memory usage
redis-cli INFO memory
```

### Queue Monitoring

```bash
# Check queue size
php artisan queue:monitor

# Failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Scheduled Tasks Monitoring

```bash
# Check scheduler is running
ps aux | grep "schedule:run"

# View scheduled tasks
php artisan schedule:list

# Test scheduler
php artisan schedule:run
```

### Performance Monitoring

**Key Metrics**:
- Page load time: < 2 seconds
- Database query time: < 100ms average
- Cache hit rate: > 80%
- Queue processing time: < 30 seconds
- File upload time: < 5 seconds per file

**Monitoring Tools**:
- New Relic
- Datadog
- Laravel Telescope
- MySQL slow query log
- Redis INFO command

### Regular Maintenance Tasks

**Daily**:
- [ ] Check error logs
- [ ] Monitor queue processing
- [ ] Verify scheduled tasks running
- [ ] Check disk space

**Weekly**:
- [ ] Review performance metrics
- [ ] Check database size growth
- [ ] Analyze slow queries
- [ ] Review cache hit rates
- [ ] Check failed jobs

**Monthly**:
- [ ] Database optimization (OPTIMIZE TABLE)
- [ ] Log rotation and archival
- [ ] Security updates
- [ ] Dependency updates
- [ ] Backup verification

---

## Troubleshooting

### Common Issues

#### Issue: Migrations Fail

**Symptoms**:
- Migration command returns error
- Database tables not created
- Data migration incomplete

**Solutions**:
```bash
# 1. Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# 2. Check migration status
php artisan migrate:status

# 3. Rollback and retry
php artisan migrate:rollback
php artisan migrate

# 4. Check for syntax errors
php artisan migrate --pretend
```

#### Issue: File Uploads Fail

**Symptoms**:
- Upload progress bar stalls
- "File too large" errors
- Files not appearing after upload

**Solutions**:
```bash
# 1. Check PHP upload limits
php -i | grep upload_max_filesize
php -i | grep post_max_size

# 2. Check disk space
df -h

# 3. Check permissions
ls -la storage/app/assignments/

# 4. Check logs
tail -f storage/logs/laravel.log | grep "upload"
```

#### Issue: Scheduled Jobs Not Running

**Symptoms**:
- Assignments not auto-publishing
- Reminders not sending
- Scheduled tasks not executing

**Solutions**:
```bash
# 1. Check cron job
crontab -l | grep schedule:run

# 2. Test scheduler manually
php artisan schedule:run

# 3. Check scheduler log
tail -f storage/logs/laravel.log | grep "schedule"

# 4. Verify command exists
php artisan schedule:list
```

#### Issue: Cache Not Clearing

**Symptoms**:
- Old data displaying
- Changes not reflecting
- Stale analytics

**Solutions**:
```bash
# 1. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Restart Redis
sudo systemctl restart redis

# 3. Check Redis connection
redis-cli PING

# 4. Flush Redis manually
redis-cli FLUSHALL
```

#### Issue: Queue Workers Not Processing

**Symptoms**:
- Notifications not sending
- Jobs stuck in queue
- Queue size growing

**Solutions**:
```bash
# 1. Check workers running
ps aux | grep "queue:work"

# 2. Restart workers
php artisan queue:restart

# 3. Start workers
php artisan queue:work --daemon &

# 4. Check failed jobs
php artisan queue:failed

# 5. Retry failed jobs
php artisan queue:retry all
```

### Performance Issues

#### Slow Page Loads

**Diagnosis**:
```bash
# 1. Enable query logging
DB::enableQueryLog();
# ... perform action ...
dd(DB::getQueryLog());

# 2. Check for N+1 queries
# Use Laravel Telescope or Debugbar

# 3. Profile with Blackfire or Xdebug
```

**Solutions**:
- Add eager loading
- Implement caching
- Optimize database queries
- Add indexes
- Use pagination

#### High Memory Usage

**Diagnosis**:
```bash
# Check PHP memory limit
php -i | grep memory_limit

# Monitor memory usage
top -p $(pgrep php)
```

**Solutions**:
- Increase PHP memory limit
- Optimize queries to use less memory
- Use chunking for large datasets
- Implement pagination
- Clear unnecessary caches

### Getting Help

**Internal Support**:
- DevOps Team: devops@example.com
- Database Team: dba@example.com
- Security Team: security@example.com

**External Resources**:
- Laravel Documentation: https://laravel.com/docs
- Laravel Forums: https://laracasts.com/discuss
- Stack Overflow: https://stackoverflow.com/questions/tagged/laravel

---

## Security Considerations

### Pre-Deployment Security

- [ ] All dependencies updated to latest secure versions
- [ ] Security audit completed
- [ ] Penetration testing performed
- [ ] OWASP Top 10 vulnerabilities addressed
- [ ] SQL injection prevention verified
- [ ] XSS protection verified
- [ ] CSRF protection enabled
- [ ] File upload validation comprehensive

### Production Security

**Environment**:
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secure
- [ ] Redis password set
- [ ] .env file not in version control
- [ ] .env file not web-accessible

**Web Server**:
- [ ] HTTPS enforced (HTTP redirects to HTTPS)
- [ ] SSL certificate valid and not expiring soon
- [ ] Security headers configured:
  - X-Frame-Options: SAMEORIGIN
  - X-Content-Type-Options: nosniff
  - X-XSS-Protection: 1; mode=block
  - Strict-Transport-Security
  - Content-Security-Policy

**File System**:
- [ ] Storage directory outside web root
- [ ] Proper file permissions (755 for directories, 644 for files)
- [ ] File upload directory not executable
- [ ] Sensitive files not web-accessible

**Database**:
- [ ] Database user has minimum required privileges
- [ ] Database accessible only from application server
- [ ] Regular backups configured
- [ ] Backup encryption enabled

### Ongoing Security

**Monthly**:
- [ ] Review access logs for suspicious activity
- [ ] Update dependencies (composer update, npm update)
- [ ] Review and rotate API keys
- [ ] Check SSL certificate expiration

**Quarterly**:
- [ ] Security audit
- [ ] Penetration testing
- [ ] Review user permissions
- [ ] Update security policies

---

## Deployment Checklist Summary

### Pre-Deployment
- [ ] Code review completed
- [ ] All tests passing
- [ ] Database backup completed
- [ ] Deployment team ready
- [ ] Stakeholders notified

### Deployment
- [ ] Maintenance mode enabled
- [ ] Code deployed
- [ ] Dependencies updated
- [ ] Migrations executed
- [ ] Caches cleared
- [ ] Permissions set
- [ ] Workers restarted
- [ ] Scheduler configured
- [ ] Maintenance mode disabled

### Post-Deployment
- [ ] Automated tests run
- [ ] Manual verification completed
- [ ] Performance verified
- [ ] Security verified
- [ ] Monitoring configured
- [ ] Documentation updated
- [ ] Team notified

### Rollback Ready
- [ ] Rollback plan reviewed
- [ ] Backup verified
- [ ] Rollback tested in staging
- [ ] Team knows rollback procedure

---

## Support Contacts

**Deployment Team**:
- Lead: deployment-lead@example.com
- DevOps: devops@example.com
- Database: dba@example.com

**Emergency Contacts**:
- On-Call Engineer: +1 (555) 123-4567
- System Administrator: +1 (555) 123-4568
- Security Team: security@example.com

**Escalation Path**:
1. On-Call Engineer
2. DevOps Lead
3. CTO

---

**Document Version**: 1.0  
**Last Updated**: February 2, 2026  
**Next Review**: May 2026

**Deployment History**:
- v1.0.0 - February 2, 2026 - Initial deployment
