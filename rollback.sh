#!/bin/bash

###############################################################################
# Pramukh Sankalp School Management System - Rollback Script
# 
# This script rolls back the application to a previous state in case of
# deployment failure or critical issues.
#
# Usage: ./rollback.sh [commit-hash] [backup-date]
# Example: ./rollback.sh abc123def 20251110_143000
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/pramukh-sankalp"
BACKUP_DIR="/var/backups/pramukh-sankalp"
COMMIT_HASH="${1}"
BACKUP_DATE="${2}"

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

print_header() {
    echo ""
    echo "=================================="
    echo "$1"
    echo "=================================="
}

# Check if running as correct user
if [ "$EUID" -eq 0 ]; then 
    print_error "Do not run this script as root"
    exit 1
fi

# Validate parameters
if [ -z "$COMMIT_HASH" ]; then
    print_error "Commit hash is required"
    echo "Usage: ./rollback.sh [commit-hash] [backup-date]"
    echo "Example: ./rollback.sh abc123def 20251110_143000"
    exit 1
fi

print_header "Pramukh Sankalp Rollback"
print_info "Starting rollback at $(date)"
print_info "Target commit: $COMMIT_HASH"
if [ -n "$BACKUP_DATE" ]; then
    print_info "Database backup: $BACKUP_DATE"
fi

# Confirmation
read -p "Are you sure you want to rollback? This action cannot be undone. (yes/no): " -r
echo
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    print_info "Rollback cancelled"
    exit 0
fi

# Navigate to application directory
cd "$APP_DIR" || exit 1

# Step 1: Enable maintenance mode
print_header "Step 1: Enabling Maintenance Mode"
php artisan down --retry=60 || {
    print_error "Failed to enable maintenance mode"
    exit 1
}
print_success "Maintenance mode enabled"

# Step 2: Revert code
print_header "Step 2: Reverting Code"
print_info "Checking out commit $COMMIT_HASH..."
git reset --hard "$COMMIT_HASH" || {
    print_error "Failed to checkout commit"
    php artisan up
    exit 1
}
print_success "Code reverted to $COMMIT_HASH"

# Step 3: Restore dependencies
print_header "Step 3: Restoring Dependencies"

print_info "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction || {
    print_error "Composer install failed"
    php artisan up
    exit 1
}
print_success "Composer dependencies installed"

print_info "Installing Node dependencies..."
npm ci --production || {
    print_error "npm install failed"
    php artisan up
    exit 1
}
print_success "Node dependencies installed"

# Step 4: Build assets
print_header "Step 4: Building Assets"
print_info "Building production assets..."
npm run build || {
    print_error "Asset build failed"
    php artisan up
    exit 1
}
print_success "Assets built successfully"

# Step 5: Rollback database (if backup provided)
if [ -n "$BACKUP_DATE" ]; then
    print_header "Step 5: Rolling Back Database"
    
    DB_BACKUP="$BACKUP_DIR/db_backup_$BACKUP_DATE.sql"
    
    if [ ! -f "$DB_BACKUP" ]; then
        print_error "Database backup not found: $DB_BACKUP"
        print_info "Available backups:"
        ls -lh "$BACKUP_DIR"/db_backup_*.sql 2>/dev/null || echo "No backups found"
        php artisan up
        exit 1
    fi
    
    print_info "Restoring database from $DB_BACKUP..."
    
    # Get database credentials from .env
    DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
    DB_PASS=$(grep DB_PASSWORD .env | cut -d '=' -f2)
    
    # Restore database
    if [[ "$DB_BACKUP" == *.gz ]]; then
        gunzip < "$DB_BACKUP" | mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" || {
            print_error "Database restore failed"
            php artisan up
            exit 1
        }
    else
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DB_BACKUP" || {
            print_error "Database restore failed"
            php artisan up
            exit 1
        }
    fi
    
    print_success "Database restored"
else
    print_header "Step 5: Rolling Back Migrations"
    print_info "Rolling back last migration batch..."
    php artisan migrate:rollback --force || {
        print_error "Migration rollback failed"
        php artisan up
        exit 1
    }
    print_success "Migrations rolled back"
fi

# Step 6: Clear and rebuild caches
print_header "Step 6: Managing Caches"

print_info "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

print_info "Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Caches rebuilt"

# Step 7: Set permissions
print_header "Step 7: Setting Permissions"
print_info "Setting file permissions..."
sudo chown -R www-data:www-data "$APP_DIR"
sudo find "$APP_DIR" -type d -exec chmod 755 {} \;
sudo find "$APP_DIR" -type f -exec chmod 644 {} \;
sudo chmod -R 775 "$APP_DIR/storage"
sudo chmod -R 775 "$APP_DIR/bootstrap/cache"
print_success "Permissions set"

# Step 8: Restart services
print_header "Step 8: Restarting Services"

print_info "Restarting PHP-FPM..."
sudo systemctl restart php8.2-fpm || {
    print_error "Failed to restart PHP-FPM"
}
print_success "PHP-FPM restarted"

print_info "Restarting queue workers..."
sudo supervisorctl restart pramukh-sankalp-worker:* || {
    print_error "Failed to restart queue workers"
}
print_success "Queue workers restarted"

print_info "Reloading Nginx..."
sudo systemctl reload nginx || {
    print_error "Failed to reload Nginx"
}
print_success "Nginx reloaded"

# Step 9: Disable maintenance mode
print_header "Step 9: Disabling Maintenance Mode"
php artisan up || {
    print_error "Failed to disable maintenance mode"
    exit 1
}
print_success "Maintenance mode disabled"

# Step 10: Run health checks
print_header "Step 10: Running Health Checks"

print_info "Checking application status..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
if [ "$HTTP_CODE" -eq 200 ]; then
    print_success "Application is responding (HTTP $HTTP_CODE)"
else
    print_error "Application returned HTTP $HTTP_CODE"
fi

print_info "Checking database connection..."
php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1 && {
    print_success "Database connection successful"
} || {
    print_error "Database connection failed"
}

# Rollback summary
print_header "Rollback Summary"
print_success "Rollback completed successfully!"
print_info "Reverted to commit: $COMMIT_HASH"
if [ -n "$BACKUP_DATE" ]; then
    print_info "Database restored from: $BACKUP_DATE"
fi
print_info "Completed at: $(date)"

echo ""
print_info "Next steps:"
echo "  1. Test critical functionality"
echo "  2. Monitor error logs: tail -f storage/logs/laravel.log"
echo "  3. Verify data integrity"
echo "  4. Notify stakeholders of rollback"

echo ""
print_success "Rollback script completed!"
