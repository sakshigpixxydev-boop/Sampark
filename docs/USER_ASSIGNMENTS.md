# User-School and User-Student Assignments

## Overview

This feature allows administrators to assign schools and students to users based on their roles. The assignment functionality is role-based and follows these rules:

- **Initiator Role**: Can be assigned to both schools and students
- **School Role**: Can be assigned to schools only
- **Other Roles**: Cannot be assigned to schools or students

## Features

### 1. School Assignment

**Accessible by**: Initiator and School roles

**Route**: `/users/{user}/assign-schools`

**Functionality**:
- View all active schools
- Select/deselect schools for assignment
- Bulk select/deselect all schools
- Save assignments

### 2. Student Assignment

**Accessible by**: Initiator role only

**Route**: `/users/{user}/assign-students`

**Functionality**:
- View all students with their details (school, standard, board)
- Search students by name or email
- Select/deselect students for assignment
- Bulk select/deselect all (filtered) students
- Save assignments

## Implementation Details

### Controller Methods

**UserController.php**:
- `assignSchools(SysUser $user)` - Display school assignment interface
- `updateSchools(Request $request, SysUser $user)` - Save school assignments
- `assignStudents(SysUser $user)` - Display student assignment interface
- `updateStudents(Request $request, SysUser $user)` - Save student assignments

### Validation

**School Assignment**:
- Only Initiator and School roles can access
- Validates that selected schools exist in the database
- Uses sync() to update assignments (removes old, adds new)

**Student Assignment**:
- Only Initiator role can access
- Validates that selected students exist in the database
- Uses sync() to update assignments (removes old, adds new)

### Database Tables

**userschool**:
- `userschool_id` (primary key)
- `sysuser_id` (foreign key to sysuser)
- `school_id` (foreign key to school)

**userstudent**:
- `userstudent_id` (primary key)
- `sysuser_id` (foreign key to sysuser)
- `student_id` (foreign key to student)

## User Interface

### Users List Page

The users index page displays action buttons based on the user's role:
- **Edit**: Available for all users
- **Schools**: Available for Initiator and School roles
- **Students**: Available for Initiator role only

### Assignment Pages

Both assignment pages feature:
- Clean, modern UI using Tailwind CSS with #00bde0 primary color
- Checkbox-based selection
- Select All / Deselect All functionality
- Search functionality (students only)
- Responsive design
- Clear visual feedback

## Usage

1. Navigate to the Users list page
2. Find the user you want to assign schools/students to
3. Click the "Schools" or "Students" link (if available for that role)
4. Select the schools/students to assign
5. Click "Save Assignments"
6. The user will now have access to the assigned schools/students

## Security

- All routes are protected by the `permission:10003` middleware (Manage Users)
- Role-based validation ensures users can only access appropriate assignment pages
- CSRF protection on all form submissions
- Input validation on all assignments

## Requirements Satisfied

This implementation satisfies the following requirements:
- **Requirement 3.1**: User registration with role selection
- **Requirement 7.5**: Initiator dashboard with assigned scope
- **Requirement 8.5**: School dashboard with school-specific data
