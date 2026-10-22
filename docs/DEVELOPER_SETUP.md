# Developer Setup Guide

## Overview

This guide will help you set up a local development environment for the Pramukh Sankalp School Management System.

## Prerequisites

### Required Software

- **PHP**: 8.2 or higher
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **npm**: 9.x or higher
- **MySQL**: 8.0+ or MariaDB 10.5+
- **Git**: Latest version

### Recommended Tools

- **IDE**: PhpStorm, VS Code, or Sublime Text
- **Database Client**: TablePlus, MySQL Workbench, or phpMyAdmin
- **API Testing**: Postman or Insomnia
- **Terminal**: iTerm2 (Mac), Windows Terminal, or Hyper

## Installation Steps

### 1. Clone Repository

```bash
git clone <repository-url>
cd pramukh-sankalp
```

### 2. Install PHP Dependencies

```bash
composer install
```

This will install all Laravel dependencies including:
- Laravel Framework
- Laravel Excel
- Spatie Laravel Permission
- Development tools (PHPUnit, Laravel Pint, etc.)

### 3. Install Node Dependencies

```bash
npm install
```

This will install:
- Vite
- Tailwind CSS
- Alpine.js
- Development tools

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pramukh_sankalp_dev
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create database:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE pramukh_sankalp_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 6. Run Migrations and Seeders

```bash
# Run migrations
php artisan migrate

# Seed master data
php artisan db:seed --class=MasterDataSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder

# Optional: Seed test data
php artisan db:seed --class=TestDataSeeder
```

### 7. Build Assets

```bash
# Development build with hot reload
npm run dev

# Or build for production
npm run build
```

### 8. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

### 9. Configure Mail (Optional)

For local development, use Mailtrap or MailHog:

**Using Mailtrap:**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@pramukh-sankalp.test"
MAIL_FROM_NAME="${APP_NAME}"
```

**Using MailHog:**

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

## Default Login Credentials

After seeding, you can log in with:

**Admin User:**
- Username: `admin`
- Password: `password`
- PIN: `1234`

**Initiator User:**
- Username: `initiator`
- Password: `password`
- PIN: `1234`

**School User:**
- Username: `school`
- Password: `password`
- PIN: `1234`

**Accountant User:**
- Username: `accountant`
- Password: `password`
- PIN: `1234`

## Development Workflow

### Running the Application

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server (hot reload)
npm run dev

# Terminal 3: Run queue worker (if needed)
php artisan queue:work
```

### Code Style

The project uses Laravel Pint for code formatting:

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/StudentManagementTest.php

# Run with coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter testStudentCreation
```

### Database Management

```bash
# Fresh migration (drops all tables)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Reset database
php artisan migrate:reset

# Check migration status
php artisan migrate:status
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache
```

### Queue Management

```bash
# Run queue worker
php artisan queue:work

# Run queue worker with specific connection
php artisan queue:work database

# Process only one job
php artisan queue:work --once

# List failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry <job-id>

# Retry all failed jobs
php artisan queue:retry all
```

## Project Structure

```
pramukh-sankalp/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controllers
│   │   ├── Middleware/      # Custom middleware
│   │   ├── Requests/        # Form requests
│   │   └── Traits/          # Reusable traits
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   ├── Notifications/       # Email notifications
│   ├── Rules/               # Custom validation rules
│   └── Helpers/             # Helper classes
├── config/                  # Configuration files
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── docs/                    # Documentation
├── public/                  # Public assets
├── resources/
│   ├── css/                 # CSS files
│   ├── js/                  # JavaScript files
│   └── views/               # Blade templates
├── routes/
│   ├── web.php              # Web routes
│   └── debug.php            # Debug routes (local only)
├── storage/                 # Storage directory
├── tests/
│   ├── Feature/             # Feature tests
│   └── Unit/                # Unit tests
└── vendor/                  # Composer dependencies
```

## Key Files

### Configuration

- `.env` - Environment configuration
- `config/app.php` - Application configuration
- `config/database.php` - Database configuration
- `config/mail.php` - Mail configuration

### Routes

- `routes/web.php` - Web routes
- `routes/debug.php` - Debug routes (local only)

### Views

- `resources/views/layouts/app.blade.php` - Main layout
- `resources/views/components/` - Reusable components
- `resources/views/auth/` - Authentication views
- `resources/views/students/` - Student management views

### Assets

- `resources/css/app.css` - Main CSS file (Tailwind)
- `resources/js/app.js` - Main JavaScript file
- `vite.config.js` - Vite configuration
- `tailwind.config.js` - Tailwind configuration

## Common Development Tasks

### Creating a New Controller

```bash
php artisan make:controller StudentController --resource
```

### Creating a New Model

```bash
php artisan make:model Student -mf
# -m: migration
# -f: factory
# -s: seeder
# -c: controller
```

### Creating a New Migration

```bash
php artisan make:migration create_students_table
```

### Creating a New Seeder

```bash
php artisan make:seeder StudentSeeder
```

### Creating a New Request

```bash
php artisan make:request StoreStudentRequest
```

### Creating a New Test

```bash
# Feature test
php artisan make:test StudentManagementTest

# Unit test
php artisan make:test StudentModelTest --unit
```

### Creating a New Middleware

```bash
php artisan make:middleware CheckPermission
```

### Creating a New Service

```bash
# Manually create in app/Services/
touch app/Services/StudentService.php
```

## Debugging

### Laravel Telescope (Optional)

Install Laravel Telescope for debugging:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access at: `http://localhost:8000/telescope`

### Debug Bar (Optional)

Install Laravel Debugbar:

```bash
composer require barryvdh/laravel-debugbar --dev
```

### Logging

```bash
# View logs
tail -f storage/logs/laravel.log

# Clear logs
> storage/logs/laravel.log
```

### Database Queries

Enable query logging in a controller:

```php
\DB::enableQueryLog();
// Your code here
dd(\DB::getQueryLog());
```

## IDE Configuration

### PhpStorm

1. Install Laravel Plugin
2. Enable Laravel support: Settings → PHP → Laravel
3. Configure PHP interpreter: Settings → PHP
4. Configure database: Database tool window
5. Configure code style: Settings → Editor → Code Style → PHP

### VS Code

Recommended extensions:
- Laravel Extension Pack
- PHP Intelephense
- Tailwind CSS IntelliSense
- Blade Formatter
- GitLens

## Git Workflow

### Branch Naming

- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `hotfix/critical-fix` - Critical fixes
- `refactor/refactor-description` - Code refactoring

### Commit Messages

Follow conventional commits:

```
feat: Add student bulk upload feature
fix: Fix invoice calculation bug
docs: Update API documentation
style: Format code with Pint
refactor: Refactor payment service
test: Add tests for student management
chore: Update dependencies
```

### Pull Request Process

1. Create feature branch from `develop`
2. Make changes and commit
3. Push branch to remote
4. Create pull request
5. Request code review
6. Address review comments
7. Merge after approval

## Troubleshooting

### Common Issues

**Port 8000 already in use:**
```bash
php artisan serve --port=8001
```

**Permission denied on storage:**
```bash
chmod -R 775 storage bootstrap/cache
```

**Class not found:**
```bash
composer dump-autoload
```

**Mix manifest not found:**
```bash
npm run build
```

**Database connection error:**
- Check MySQL is running
- Verify credentials in `.env`
- Check database exists

**CSRF token mismatch:**
```bash
php artisan cache:clear
php artisan config:clear
```

### Getting Help

- Check Laravel documentation: https://laravel.com/docs
- Review project documentation in `docs/`
- Check existing issues on GitHub
- Ask team members
- Review error logs in `storage/logs/`

## Best Practices

### Code Organization

- Keep controllers thin, move logic to services
- Use form requests for validation
- Use resource controllers for CRUD operations
- Follow PSR-12 coding standards
- Write meaningful comments

### Database

- Always use migrations for schema changes
- Use factories for test data
- Index foreign keys and frequently queried columns
- Use transactions for multiple related operations

### Security

- Never commit `.env` file
- Use CSRF protection on all forms
- Validate and sanitize all inputs
- Use prepared statements (Eloquent does this)
- Hash passwords and PINs

### Testing

- Write tests for new features
- Maintain test coverage above 70%
- Test both happy path and edge cases
- Use factories for test data
- Mock external services

### Performance

- Use eager loading to avoid N+1 queries
- Cache frequently accessed data
- Optimize database queries
- Use pagination for large datasets
- Minimize asset size

## Additional Resources

### Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/start-here)
- [MySQL Documentation](https://dev.mysql.com/doc/)

### Learning Resources

- [Laracasts](https://laracasts.com/)
- [Laravel Daily](https://laraveldaily.com/)
- [Laravel News](https://laravel-news.com/)

### Community

- [Laravel Discord](https://discord.gg/laravel)
- [Laravel Forums](https://laracasts.com/discuss)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel)

---

*Last Updated: November 2025*
*Version: 1.0*
