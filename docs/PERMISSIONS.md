# Permission System Documentation

## Overview

The permission system provides role-based access control (RBAC) for the Pramukh Sankalp School Management System. It allows administrators to create roles, assign permissions to roles, and control access to different parts of the application.

## Components

### 1. Roles
- Roles define groups of users with similar access levels (e.g., Admin, Initiator, School, Accountant)
- Roles can be marked as "predefined" to prevent deletion
- Each role can have multiple permissions assigned

### 2. Permissions
- Permissions define specific access rights within the system
- Each permission has a unique numeric key (e.g., 1000, 1001, 1002)
- Permissions have descriptive names (e.g., "Manage Users", "View Reports")

### 3. Middleware
- The `CheckPermission` middleware protects routes based on permission keys
- Users must have the required permission through their role to access protected routes

## Usage

### Managing Roles

**List Roles:**
```
GET /roles
```

**Create Role:**
```
GET /roles/create
POST /roles
```

**Edit Role:**
```
GET /roles/{role}/edit
PUT /roles/{role}
```

**Delete Role:**
```
DELETE /roles/{role}
```
Note: Predefined roles cannot be deleted.

### Managing Permissions

**List Permissions:**
```
GET /permissions
```

**Create Permission:**
```
GET /permissions/create
POST /permissions
```

**Edit Permission:**
```
GET /permissions/{permission}/edit
PUT /permissions/{permission}
```

### Protecting Routes with Permissions

To protect a route with a permission, use the `permission` middleware:

```php
// Single route
Route::get('/admin/users', [UserController::class, 'index'])
    ->middleware('permission:1000');

// Route group
Route::middleware(['auth', 'permission:1000'])->group(function () {
    Route::resource('users', UserController::class);
});
```

### Checking Permissions in Code

```php
// Check if user has permission
$user = auth()->user();
$hasPermission = $user->role->permissions()
    ->where('permkey', 1000)
    ->exists();

if ($hasPermission) {
    // User has permission
}
```

### Checking Permissions in Blade Views

```blade
@if(auth()->user()->role->permissions->contains('permkey', 1000))
    <!-- Show content for users with permission 1000 -->
@endif
```

## Permission Key Convention

Use a systematic numbering scheme for permission keys:

- 1000-1999: User Management
- 2000-2999: Role & Permission Management
- 3000-3999: School Management
- 4000-4999: Student Management
- 5000-5999: Financial Management
- 6000-6999: Report Management

Example:
- 1000: View Users
- 1001: Create Users
- 1002: Edit Users
- 1003: Delete Users

## Error Handling

When a user tries to access a route without the required permission:
- HTTP 403 Forbidden error is returned
- Custom error page is displayed at `resources/views/errors/403.blade.php`
- User can navigate back or go to dashboard

## Database Schema

### role table
- `role_id`: Primary key
- `rolename`: Role name (unique)
- `ispredefined`: Boolean flag (1 = cannot be deleted)

### permission table
- `permission_id`: Primary key
- `permkey`: Numeric permission key (unique)
- `permname`: Permission description

### rolepermission table
- `rolepermission_id`: Primary key
- `role_id`: Foreign key to role
- `permission_id`: Foreign key to permission

## Best Practices

1. **Use Predefined Roles Carefully**: Only mark system-critical roles as predefined
2. **Consistent Permission Keys**: Follow the numbering convention
3. **Descriptive Permission Names**: Use clear, action-oriented names
4. **Test Permissions**: Always test permission checks after implementation
5. **Document Permissions**: Keep a list of all permission keys and their purposes
6. **Minimal Permissions**: Grant only necessary permissions to each role
7. **Regular Audits**: Periodically review role-permission assignments

## Example: Setting Up a New Feature

1. Create permissions for the feature:
   ```
   7000: View Reports
   7001: Generate Reports
   7002: Export Reports
   ```

2. Assign permissions to appropriate roles:
   - Admin: All permissions (7000, 7001, 7002)
   - Accountant: View and generate (7000, 7001)
   - School: View only (7000)

3. Protect routes:
   ```php
   Route::middleware(['auth', 'permission:7000'])->group(function () {
       Route::get('/reports', [ReportController::class, 'index']);
       Route::get('/reports/generate', [ReportController::class, 'generate'])
           ->middleware('permission:7001');
       Route::get('/reports/export', [ReportController::class, 'export'])
           ->middleware('permission:7002');
   });
   ```

4. Update UI based on permissions:
   ```blade
   @if(auth()->user()->role->permissions->contains('permkey', 7001))
       <a href="/reports/generate">Generate Report</a>
   @endif
   ```
