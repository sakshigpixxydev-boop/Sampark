#!/bin/bash

###############################################################################
# Pramukh Sankalp School Management System - Deployment Script
# 
# This script automates the deployment process for the application.
# It handles code updates, dependency installation, database migrations,
# cache management, and service restarts.
#
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/pramukh-sankalp"
ENVIRONMENT="${1:-production}"
BACKUP_DIR="/var/backups/pramukh-sankalp"
DATE=$(date +%Y%m%d_%H%M%S)

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

# Verify environment
if [ "$ENVIRONMENT" != "production" ] && [ "$ENVIRONMENT" != "staging" ]; then
    print_error "Invalid environment. Use 'production' or 'staging'"
    exit 1
fi

print_header "Pramukh Sankalp Deployment - $ENVIRONMENT"
print_info "Starting deployment at $(date)"

# Navigate to application directory
cd "$APP_DIR" || exit 1

# Step 1: Create backup
print_header "Step 1: Creating Backup"
mkdir -p "$BACKUP_DIR"

print_info "Backing up database..."
php artisan db:backup --path="$BACKUP_DIR/db_backup_$DATE.sql" || {
    print_error "Database backup failed"
    exit 1
}
print_success "Database backup created"

print_info "Backing up .env file..."
cp .env "$BACKUP_DIR/env_backup_$DATE" || {
    print_error ".env backup failed"
    exit 1
}
print_success ".env file backed up"

# Step 2: Enable maintenance mode
print_header "Step 2: Enabling Maintenance Mode"
php artisan down --retry=60 || {
    print_error "Failed to enable maintenance mode"
    exit 1
}
print_success "Maintenance mode enabled"

# Step 3: Pull latest code
print_header "Step 3: Pulling Latest Code"
print_info "Fetching from repository..."
git fetch origin || {
    print_error "Git fetch failed"
    php artisan up
    exit 1
}

print_info "Pulling changes..."
git pull origin main || {
    print_error "Git pull failed"
    php artisan up
    exit 1
}
print_success "Code updated successfully"

# Step 4: Install dependencies
print_header "Step 4: Installing Dependencies"

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

# Step 5: Build assets
print_header "Step 5: Building Assets"
print_info "Building production assets..."
npm run build || {
    print_error "Asset build failed"
    php artisan up
    exit 1
}
print_success "Assets built successfully"

# Step 6: Run database migrations
print_header "Step 6: Running Database Migrations"
print_info "Checking for pending migrations..."
php artisan migrate --force || {
    print_error "Migration failed"
    print_info "Rolling back..."
    php artisan migrate:rollback --force
    php artisan up
    exit 1
}
print_success "Migrations completed"

# Step 7: Clear and rebuild caches
print_header "Step 7: Managing Caches"

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

# Step 8: Set permissions
print_header "Step 8: Setting Permissions"
print_info "Setting file permissions..."
sudo chown -R www-data:www-data "$APP_DIR"
sudo find "$APP_DIR" -type d -exec chmod 755 {} \;
sudo find "$APP_DIR" -type f -exec chmod 644 {} \;
sudo chmod -R 775 "$APP_DIR/storage"
sudo chmod -R 775 "$APP_DIR/bootstrap/cache"
print_success "Permissions set"

# Step 9: Restart services
print_header "Step 9: Restarting Services"

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

# Step 10: Disable maintenance mode
print_header "Step 10: Disabling Maintenance Mode"
php artisan up || {
    print_error "Failed to disable maintenance mode"
    exit 1
}
print_success "Maintenance mode disabled"

# Step 11: Run health checks
print_header "Step 11: Running Health Checks"

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

# Deployment summary
print_header "Deployment Summary"
print_success "Deployment completed successfully!"
print_info "Environment: $ENVIRONMENT"
print_info "Completed at: $(date)"
print_info "Backup location: $BACKUP_DIR"
print_info "Database backup: db_backup_$DATE.sql"
print_info "Env backup: env_backup_$DATE"

echo ""
print_info "Next steps:"
echo "  1. Test critical functionality"
echo "  2. Monitor error logs: tail -f storage/logs/laravel.log"
echo "  3. Check queue processing: php artisan queue:work --once"
echo "  4. Review application metrics"

echo ""
print_success "Deployment script completed!"
