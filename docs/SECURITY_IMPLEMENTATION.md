# Security Implementation Guide

## Overview

This document describes the security features implemented in the Pramukh Sankalp School Management System.

## 1. CSRF Protection

### Implementation
- All forms include `@csrf` directive for CSRF token validation
- Laravel's built-in CSRF middleware is enabled by default
- Session configuration includes secure cookie settings

### Configuration
- **Session Cookie**: HTTP-only, SameSite=lax
- **CSRF Token**: Automatically validated on all POST, PUT, PATCH, DELETE requests
- **Token Lifetime**: Matches session lifetime (120 minutes by default)

### Usage
```blade
<form method="POST" action="{{ route('example.store') }}">
    @csrf
    <!-- form fields -->
</form>
```

## 2. Input Validation and Sanitization

### Custom Validation Rules

#### SafeString
Prevents XSS attacks by checking for dangerous HTML and JavaScript patterns.

```php
use App\Rules\SafeString;

'field_name' => ['required', 'string', new SafeString()]
```

#### ValidPin
Validates that PIN is exactly 4 digits.

```php
use App\Rules\ValidPin;

'pin' => ['required', new ValidPin()]
```

#### ValidPhoneNumber
Validates that phone number is exactly 10 digits.

```php
use App\Rules\ValidPhoneNumber;

'phone' => ['required', new ValidPhoneNumber()]
```

### Form Request Classes

Dedicated Form Request classes for major entities:
- `StoreUserRequest` / `UpdateUserRequest`
- `StoreStudentRequest` / `UpdateStudentRequest`
- `StoreSchoolRequest` / `UpdateSchoolRequest`

These classes include:
- Validation rules
- Input sanitization via `prepareForValidation()`
- Custom error messages

### Sanitization Middleware

The `SanitizeInput` middleware automatically:
- Removes null bytes from all inputs
- Trims whitespace
- Skips password/PIN fields
- Skips file uploads

Applied globally to all web routes.

### Sanitization Trait

The `SanitizesInput` trait provides helper methods:
- `sanitizeString()` - Remove HTML tags and trim
- `sanitizeArray()` - Sanitize array of strings
- `sanitizeEmail()` - Filter email addresses
- `sanitizeNumeric()` - Keep only digits
- `sanitizePhone()` - Keep only digits from phone numbers

## 3. File Upload Security

### FileUploadService

Comprehensive file validation service that checks:
- File upload success
- File size limits (10MB for CSV, 2MB for images)
- MIME type validation
- File extension validation
- Null byte detection
- Malicious content scanning

### Custom Validation Rules

#### SecureCsvFile
Validates CSV file uploads with security checks.

```php
use App\Rules\SecureCsvFile;

'csv_file' => ['required', 'file', new SecureCsvFile()]
```

#### SecureImageFile
Validates image file uploads with security checks.

```php
use App\Rules\SecureImageFile;

'logo' => ['nullable', 'file', new SecureImageFile()]
```

### Malicious Content Detection

The system scans for:
- Script tags
- JavaScript protocols
- Event handlers (onclick, onload, etc.)
- Iframes, objects, embeds
- PHP execution functions (eval, exec, system, etc.)
- Base64 encoding attempts

### Secure File Storage

- CSV files stored in private storage (not publicly accessible)
- Images stored in public storage with sanitized filenames
- Unique UUID-based filenames to prevent overwrites
- Filename sanitization to remove special characters

## 4. Audit Logging

### AuditLog Model

Tracks all significant system events with:
- Event type (CREATE, UPDATE, DELETE, LOGIN, LOGOUT, etc.)
- User who performed the action
- Model being modified (polymorphic relationship)
- Old and new values (for updates)
- Request metadata (IP, user agent, URL, method)
- Timestamp

### AuditLoggerService

Provides methods for logging:
- `logCreate()` - Log model creation
- `logUpdate()` - Log model updates with old/new values
- `logDelete()` - Log model deletion
- `logAuth()` - Log authentication events
- `logPayment()` - Log payment transactions
- `logCustom()` - Log custom events

### Auditable Trait

Add to models for automatic audit logging:

```php
use App\Models\Traits\Auditable;

class Student extends Model
{
    use Auditable;
    // ...
}
```

This automatically logs:
- Model creation
- Model updates (with change tracking)
- Model deletion

### Authentication Logging

All authentication events are logged:
- Successful logins (with auth method)
- Failed login attempts (with reason)
- User not found attempts
- Inactive user attempts
- Logout events

### Sensitive Data Protection

The audit logger automatically redacts:
- Passwords
- PINs
- Tokens
- Secrets
- API keys

### Audit Log Viewer

Admin users can view audit logs at `/audit-logs` with:
- Filtering by event type, user, model, date range
- Search functionality
- Detailed view of each log entry
- Old/new value comparison
- Request metadata display

### Database Schema

```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(50),
    auditable_type VARCHAR(255),
    auditable_id BIGINT,
    user_id BIGINT,
    user_type VARCHAR(255),
    description TEXT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    url VARCHAR(500),
    method VARCHAR(10),
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX (auditable_type, auditable_id),
    INDEX (user_id, user_type),
    INDEX (event_type),
    INDEX (created_at)
);
```

## Security Best Practices

### 1. Password Security
- Passwords hashed using bcrypt
- Minimum 8 characters required
- PINs hashed using bcrypt
- No password/PIN stored in plain text

### 2. Session Security
- HTTP-only cookies
- SameSite=lax protection
- Session regeneration on login
- Session invalidation on logout

### 3. Input Validation
- All user inputs validated
- Type checking enforced
- Length limits applied
- Format validation (email, phone, etc.)

### 4. Output Encoding
- Blade templates auto-escape output
- JSON responses properly encoded
- HTML special characters escaped

### 5. File Upload Security
- File type validation
- File size limits
- Malicious content scanning
- Secure storage locations
- Unique filenames

### 6. Database Security
- Prepared statements (Eloquent ORM)
- No raw SQL queries with user input
- Foreign key constraints
- Proper indexing

### 7. Authentication Security
- Inactive user check
- Failed login tracking
- Audit logging of all auth events
- Session management

### 8. Authorization Security
- Role-based access control
- Permission middleware
- Route protection
- Resource ownership checks

## Monitoring and Maintenance

### Regular Tasks

1. **Review Audit Logs**
   - Check for suspicious activity
   - Monitor failed login attempts
   - Review data modifications

2. **Update Dependencies**
   - Keep Laravel and packages updated
   - Apply security patches promptly

3. **Backup Audit Logs**
   - Regular database backups
   - Archive old audit logs
   - Maintain log retention policy

4. **Security Scanning**
   - Regular vulnerability scans
   - Code review for security issues
   - Penetration testing

### Incident Response

If a security incident is detected:

1. Check audit logs for affected records
2. Identify the scope of the breach
3. Disable affected user accounts if necessary
4. Review and patch the vulnerability
5. Notify affected users if required
6. Document the incident and response

## Configuration

### Environment Variables

```env
# Session Security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# File Upload
MAX_CSV_SIZE=10240  # 10MB in KB
MAX_IMAGE_SIZE=2048  # 2MB in KB

# Audit Logging
AUDIT_LOG_RETENTION_DAYS=365
```

### Middleware Configuration

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
        'sanitize' => \App\Http\Middleware\SanitizeInput::class,
    ]);
    
    $middleware->web(append: [
        \App\Http\Middleware\SanitizeInput::class,
    ]);
})
```

## Testing Security Features

### Manual Testing

1. **CSRF Protection**
   - Try submitting forms without CSRF token
   - Verify 419 error is returned

2. **Input Validation**
   - Submit forms with invalid data
   - Try XSS payloads in text fields
   - Test SQL injection attempts

3. **File Upload**
   - Upload non-CSV files to CSV endpoint
   - Upload oversized files
   - Upload files with malicious content

4. **Authentication**
   - Test with invalid credentials
   - Test with inactive user
   - Verify audit logs are created

### Automated Testing

```php
// Example test for CSRF protection
public function test_form_submission_requires_csrf_token()
{
    $response = $this->post('/students', [
        'firstname' => 'John',
        // ... other fields
    ]);
    
    $response->assertStatus(419); // CSRF token mismatch
}

// Example test for input sanitization
public function test_xss_attempt_is_blocked()
{
    $response = $this->post('/students', [
        'firstname' => '<script>alert("XSS")</script>',
        // ... other fields
    ]);
    
    $response->assertSessionHasErrors();
}
```

## Compliance

This security implementation helps meet requirements for:
- OWASP Top 10 protection
- Data protection regulations
- Audit trail requirements
- Access control standards

## Support

For security concerns or questions:
- Review this documentation
- Check audit logs for suspicious activity
- Contact system administrator
- Report security vulnerabilities privately
