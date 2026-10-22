# Deployment Runbook

## Overview

This runbook provides step-by-step instructions for deploying the Pramukh Sankalp School Management System to staging and production environments.

## Prerequisites

Before starting deployment, ensure:
- [ ] All code changes are merged to main branch
- [ ] All tests are passing
- [ ] Code review is complete
- [ ] Deployment checklist is reviewed
- [ ] Stakeholders are notified
- [ ] Maintenance window is scheduled
- [ ] Backup is recent and verified

## Deployment Environments

### Staging Environment
- **Purpose**: Pre-production testing
- **URL**: https://staging.your-domain.com
- **Database**: pramukh_sankalp_staging
- **Deployment Frequency**: As needed for testing

### Production Environment
- **Purpose**: Live application
- **URL**: https://your-domain.com
- **Database**: pramukh_sankalp
- **Deployment Frequency**: Scheduled releases

## Staging Deployment

### Step 1: Prepare for Deployment

```bash
# SSH into staging server
ssh user@staging-server

# Navigate to application directory
cd /var/www/pramukh-sankalp

# Check current status
git status
git log -1
```

### Step 2: Create Backup

```bash
# Create database backup
php artisan db:backup --path=/var/backups/pramukh-sankalp/staging_$(date +%Y%m%d_%H%M%S).sql

# Backup .env file
cp .env /var/backups/pramukh-sankalp/env_staging_$(date +%Y%m%d_%H%M%S)

# Verify backups
ls -lh /var/backups/pramukh-sankalp/
```

### Step 3: Deploy Using Script

```bash
# Run deployment script
./deploy.sh staging

# Monitor deployment output
# Script will handle:
# - Enabling maintenance mode
# - Pulling latest code
# - Installing dependencies
# - Building assets
# - Running migrations
# - Clearing/rebuilding caches
# - Restarting services
# - Disabling maintenance mode
```

### Step 4: Verify Deployment

```bash
# Check application status
curl -I https://staging.your-domain.com

# Check error logs
tail -f storage/logs/laravel.log

# Test database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check queue processing
php artisan queue:work --once

# Verify scheduled tasks
php artisan schedule:list
```

### Step 5: Test Functionality

Test critical features:
- [ ] Login with all user roles
- [ ] Create student
- [ ] Bulk upload students
- [ ] Generate invoice
- [ ] Record payment
- [ ] Accountant batch verification
- [ ] Email notifications
- [ ] Search functionality
- [ ] Reports generation

### Step 6: Monitor

```bash
# Monitor error logs
tail -f storage/logs/laravel.log

# Monitor web server logs
sudo tail -f /var/log/nginx/pramukh-sankalp-error.log

# Monitor queue workers
sudo supervisorctl status pramukh-sankalp-worker:*

# Check system resources
htop
df -h
```

## Production Deployment

### Pre-Deployment Meeting

Hold a brief meeting 30 minutes before deployment:
- Review deployment plan
- Confirm all prerequisites met
- Assign roles and responsibilities
- Review rollback procedure
- Confirm communication plan

### Step 1: Notify Stakeholders

Send notification email:

```
Subject: Scheduled Maintenance - [Date] [Time]

Dear Users,

We will be performing scheduled maintenance on [Date] from [Start Time] to [End Time].

During this time, the application will be unavailable. We apologize for any inconvenience.

Expected downtime: [Duration]

Thank you for your patience.

Best regards,
Pramukh Sankalp Team
```

### Step 2: Enable Maintenance Page

```bash
# SSH into production server
ssh user@production-server

# Navigate to application directory
cd /var/www/pramukh-sankalp

# Enable maintenance mode with custom message
php artisan down --retry=60 --message="System maintenance in progress. We'll be back shortly."
```

### Step 3: Create Production Backup

```bash
# Create comprehensive backup
BACKUP_DATE=$(date +%Y%m%d_%H%M%S)

# Database backup
mysqldump -u username -p pramukh_sankalp | gzip > /var/backups/pramukh-sankalp/prod_db_$BACKUP_DATE.sql.gz

# File backup
tar -czf /var/backups/pramukh-sankalp/prod_files_$BACKUP_DATE.tar.gz storage/

# Backup .env
cp .env /var/backups/pramukh-sankalp/prod_env_$BACKUP_DATE

# Verify backups
ls -lh /var/backups/pramukh-sankalp/
```

### Step 4: Deploy to Production

```bash
# Run deployment script
./deploy.sh production

# Monitor deployment closely
# Watch for any errors or warnings
```

### Step 5: Post-Deployment Verification

#### Application Health Check

```bash
# Check HTTP response
curl -I https://your-domain.com

# Should return: HTTP/2 200

# Check application version
php artisan --version

# Check environment
php artisan env

# Should show: production
```

#### Database Verification

```bash
# Check database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Verify migrations
php artisan migrate:status

# Check database size
mysql -u username -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = 'pramukh_sankalp' GROUP BY table_schema;"
```

#### Service Verification

```bash
# Check PHP-FPM
sudo systemctl status php8.2-fpm

# Check Nginx
sudo systemctl status nginx

# Check queue workers
sudo supervisorctl status pramukh-sankalp-worker:*

# Check cron jobs
sudo crontab -l -u www-data
```

#### Performance Check

```bash
# Check response time
time curl -I https://your-domain.com

# Check server resources
free -h
df -h
uptime
```

### Step 6: Functional Testing

Test all critical functionality:

#### Authentication
- [ ] Login with admin account
- [ ] Login with initiator account
- [ ] Login with school account
- [ ] Login with accountant account
- [ ] Test password reset
- [ ] Test PIN reset

#### Student Management
- [ ] Create individual student
- [ ] Edit student information
- [ ] View student list
- [ ] Filter students
- [ ] Search students

#### Bulk Upload
- [ ] Upload CSV file
- [ ] Preview students
- [ ] Submit for verification
- [ ] Verify batch appears in accountant queue

#### Accountant Verification
- [ ] View pending batches
- [ ] Review batch details
- [ ] Approve batch with payment
- [ ] Verify students are registered

#### Invoice & Payment
- [ ] Generate invoice
- [ ] View invoice details
- [ ] Record payment
- [ ] Update payment status

#### Email Notifications
- [ ] Test password reset email
- [ ] Test batch rejection email
- [ ] Test payment confirmation email

#### Reports & Search
- [ ] Global search
- [ ] Advanced filtering
- [ ] Dashboard metrics
- [ ] Audit logs

### Step 7: Monitor Production

#### First 15 Minutes

```bash
# Watch error logs continuously
tail -f storage/logs/laravel.log

# Monitor web server logs
sudo tail -f /var/log/nginx/pramukh-sankalp-error.log

# Check for any 500 errors
sudo grep "500" /var/log/nginx/pramukh-sankalp-access.log | tail -20
```

#### First Hour

- Monitor error tracking dashboard (Sentry)
- Check application performance metrics
- Monitor server resources (CPU, memory, disk)
- Review user feedback/reports
- Check queue processing

#### First Day

- Review error rates and trends
- Analyze performance metrics
- Check database performance
- Monitor disk space usage
- Review user activity logs

### Step 8: Notify Stakeholders

Send completion notification:

```
Subject: Maintenance Complete - System Available

Dear Users,

The scheduled maintenance has been completed successfully. The application is now available.

New features/improvements:
- [List major changes]

If you experience any issues, please contact support.

Thank you for your patience.

Best regards,
Pramukh Sankalp Team
```

## Rollback Procedure

If critical issues are discovered:

### Step 1: Assess Situation

- Identify the issue
- Determine severity
- Decide if rollback is necessary
- Notify team

### Step 2: Execute Rollback

```bash
# Enable maintenance mode
php artisan down

# Get previous commit hash
git log -5 --oneline

# Run rollback script
./rollback.sh <commit-hash> <backup-date>

# Example:
# ./rollback.sh abc123def 20251110_143000
```

### Step 3: Verify Rollback

- Test critical functionality
- Check error logs
- Verify data integrity
- Monitor performance

### Step 4: Communicate

- Notify stakeholders of rollback
- Explain reason for rollback
- Provide timeline for fix
- Plan for re-deployment

## Troubleshooting

### Issue: 500 Internal Server Error

**Diagnosis:**
```bash
# Check error logs
tail -100 storage/logs/laravel.log

# Check web server logs
sudo tail -100 /var/log/nginx/pramukh-sankalp-error.log

# Check PHP-FPM logs
sudo tail -100 /var/log/php8.2-fpm.log
```

**Common Causes:**
- File permissions incorrect
- .env configuration error
- Cache corruption
- Database connection failure

**Resolution:**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/pramukh-sankalp
sudo chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

### Issue: Database Connection Error

**Diagnosis:**
```bash
# Test database connection
mysql -u username -p -e "SELECT 1;"

# Check MySQL status
sudo systemctl status mysql

# Check .env configuration
grep DB_ .env
```

**Resolution:**
```bash
# Restart MySQL
sudo systemctl restart mysql

# Verify credentials
php artisan tinker --execute="DB::connection()->getPdo();"

# Check database exists
mysql -u username -p -e "SHOW DATABASES;"
```

### Issue: Queue Jobs Not Processing

**Diagnosis:**
```bash
# Check supervisor status
sudo supervisorctl status pramukh-sankalp-worker:*

# Check worker logs
tail -100 storage/logs/worker.log

# Check failed jobs
php artisan queue:failed
```

**Resolution:**
```bash
# Restart workers
sudo supervisorctl restart pramukh-sankalp-worker:*

# Retry failed jobs
php artisan queue:retry all

# Clear queue
php artisan queue:flush
```

### Issue: High Memory Usage

**Diagnosis:**
```bash
# Check memory usage
free -h

# Check PHP memory limit
php -i | grep memory_limit

# Check processes
ps aux --sort=-%mem | head -10
```

**Resolution:**
```bash
# Increase PHP memory limit in php.ini
sudo nano /etc/php/8.2/fpm/php.ini
# memory_limit = 512M

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Issue: Slow Response Times

**Diagnosis:**
```bash
# Check server load
uptime

# Check slow queries
sudo mysql -u root -p -e "SHOW FULL PROCESSLIST;"

# Check application logs
grep "slow" storage/logs/laravel.log
```

**Resolution:**
```bash
# Enable OPcache
sudo nano /etc/php/8.2/fpm/conf.d/10-opcache.ini
# opcache.enable=1

# Optimize database
mysql -u root -p -e "OPTIMIZE TABLE pramukh_sankalp.*;"

# Clear and rebuild caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

## Post-Deployment Tasks

### Immediate (Within 1 Hour)
- [ ] Monitor error logs
- [ ] Check application performance
- [ ] Verify critical functionality
- [ ] Monitor server resources
- [ ] Respond to user reports

### Short-term (Within 24 Hours)
- [ ] Review error rates
- [ ] Analyze performance metrics
- [ ] Check database performance
- [ ] Monitor disk space
- [ ] Review user feedback
- [ ] Update documentation
- [ ] Create deployment report

### Long-term (Within 1 Week)
- [ ] Analyze performance trends
- [ ] Review security logs
- [ ] Optimize slow queries
- [ ] Plan next release
- [ ] Conduct retrospective

## Deployment Report Template

```markdown
# Deployment Report

**Date:** [Date]
**Environment:** [Staging/Production]
**Version:** [Version Number]
**Deployed By:** [Name]

## Summary
[Brief summary of deployment]

## Changes Deployed
- [List of features/fixes]

## Deployment Timeline
- Start Time: [Time]
- End Time: [Time]
- Total Duration: [Duration]
- Downtime: [Duration]

## Issues Encountered
- [List any issues and resolutions]

## Rollback
- Rollback Required: [Yes/No]
- Rollback Reason: [If applicable]

## Post-Deployment Metrics
- Error Rate: [Percentage]
- Response Time: [Milliseconds]
- Uptime: [Percentage]

## Testing Results
- [Summary of testing]

## Recommendations
- [Any recommendations for future deployments]

## Sign-Off
- Developer: [Name] - [Date]
- DevOps: [Name] - [Date]
- QA: [Name] - [Date]
```

## Emergency Contacts

### Technical Team
- **Lead Developer:** [Name] - [Phone] - [Email]
- **DevOps Engineer:** [Name] - [Phone] - [Email]
- **Database Administrator:** [Name] - [Phone] - [Email]

### Business Team
- **Project Manager:** [Name] - [Phone] - [Email]
- **Product Owner:** [Name] - [Phone] - [Email]

### External Services
- **Hosting Provider:** [Support Contact]
- **SSL Provider:** [Support Contact]
- **Email Provider:** [Support Contact]

---

*Last Updated: November 2025*
*Version: 1.0*
