# Admin User Guide - Pramukh Sankalp School Management System

## Introduction

Welcome to the Pramukh Sankalp School Management System. As an Admin user, you have full access to all system features and can manage users, schools, students, payments, and system configuration.

## Getting Started

### Logging In

1. Navigate to the login page
2. Enter your username
3. Choose authentication method:
   - **Password**: Enter your password
   - **PIN**: Enter your 4-digit PIN
4. Click "Login"

**Note**: If your account is marked as inactive, you will not be able to log in. Contact the system administrator.

### Dashboard Overview

After logging in, you'll see the Admin Dashboard with:

- **Overview Cards**: Total students, schools, and initiators
- **Student Payments**: Pending payments with school filters
- **New Inquiries**: Recent student registrations with payment status filters

Click any overview card to navigate to the detailed list page.

## User Management

### Creating Users

1. Navigate to **Users** → **Create User**
2. Fill in required fields:
   - Role (Admin, Initiator, School, Accountant)
   - First Name
   - Username (must be unique)
   - Password or PIN (at least one required)
3. Optional fields:
   - User Type
   - Inactive Status (default: Active)
4. Click **Save**

### Managing User Assignments

**Assign Schools to Users:**
1. Go to **Users** → Select user → **Assign Schools**
2. Select schools from the list
3. Click **Save Assignments**

**Assign Students to Users:**
1. Go to **Users** → Select user → **Assign Students**
2. Select students from the list
3. Click **Save Assignments**

### Changing Password/PIN

1. Go to **Profile** → **Change Password** or **Change PIN**
2. Enter current password/PIN
3. Enter new password/PIN
4. Confirm new password/PIN
5. Click **Update**

## Role and Permission Management

### Managing Roles

1. Navigate to **Roles** → **Create Role**
2. Enter role name
3. Select permissions to assign
4. Click **Save**

**Note**: Predefined roles (Admin, Initiator, School, Accountant) cannot be deleted.

### Managing Permissions

1. Navigate to **Permissions** → **Create Permission**
2. Enter permission key (numeric, e.g., 1000)
3. Enter permission description
4. Click **Save**

## School Management

### Adding Schools

1. Navigate to **Schools** → **Create School**
2. Fill in required fields:
   - School Name
   - School Type
   - Educational Board
   - Admin Name
   - Admin Contact (10 digits)
3. Optional fields:
   - Logo upload
   - Location (Country, State, District, Taluka)
   - Status (0=Lead, 1=Final)
   - Inactive Status
4. Click **Save**

### Filtering Schools

Use the filter panel to search by:
- School Type
- Educational Board
- Location
- Status

## Student Management

### Individual Student Entry

1. Navigate to **Students** → **Create Student**
2. Fill in required fields:
   - Student Type
   - Educational Board
   - Standard
   - Taluka
   - First Name, Middle Name, Last Name
   - Email (must be unique)
3. Optional fields:
   - Gender, Birthdate
   - Address, Contact Numbers
   - Guardian Information
4. Click **Save**

### Bulk Student Upload

See the [Bulk Upload Guide](BULK_UPLOAD_GUIDE.md) for detailed instructions.

**Quick Steps:**
1. Navigate to **Students** → **Bulk Upload**
2. Download CSV template
3. Fill in student data
4. Select Semester Batch
5. Upload CSV file
6. Review preview with calculated fees
7. Edit any records if needed
8. Submit for verification

### Filtering Students

Use advanced filters to search by:
- School
- Assigned User
- Payment Status
- City
- Standard
- Educational Board
- Student Type

## Guardian Management

### Adding Guardians

1. Navigate to **Guardians** → **Create Guardian**
2. Fill in required fields:
   - Relation (Father, Mother, etc.)
   - First Name, Middle Name, Last Name
3. Optional fields:
   - Contact information
4. Associate with student
5. Click **Save**

**Note**: Multiple guardians can be added per student.

## Fee and Invoice Management

### Creating Fee Plans

1. Navigate to **Fee Plans** → **Create Fee Plan**
2. Select scope:
   - School
   - Standard
   - Semester Batch
3. Enter fee components and amounts
4. Set validity dates
5. Click **Save**

### Managing Discount Rules

1. Navigate to **Discount Rules** → **Create Discount Rule**
2. Select scope:
   - **SCHOOL**: Applies to all students in a school
   - **STUDENT**: Applies to specific student
3. Select discount type:
   - **FLAT**: Fixed amount discount
   - **PERCENT**: Percentage discount
4. Enter discount value
5. Specify which fee components it applies to
6. Set validity dates
7. Click **Save**

### Generating Invoices

1. Navigate to **Invoices** → **Create Invoice**
2. Select student
3. System automatically:
   - Fetches applicable fee plans
   - Calculates subtotal
   - Applies discount rules
   - Calculates total due
4. Review and confirm
5. Click **Generate**

### Invoice Status

- **DRAFT**: Not yet issued
- **ISSUED**: Sent to student/school
- **PARTIAL**: Partially paid
- **PAID**: Fully paid
- **VOID**: Cancelled

## Payment Management

### Recording Payments

1. Navigate to **Payments** → **Create Payment**
2. Fill in required fields:
   - Student
   - Payment Mode
   - Payment Date
   - Transaction ID
3. Enter amount
4. Click **Save**

### Payment Status

- **PENDING**: Awaiting processing
- **AUTHORIZED**: Approved but not completed
- **PAID**: Successfully completed
- **FAILED**: Transaction failed
- **REFUNDED**: Payment returned
- **CANCELLED**: Transaction cancelled

## Parcel Management

### Recording Parcels

1. Navigate to **Parcels** → **Create Parcel**
2. Fill in required fields:
   - Student
   - Parcel Company
   - Tracking Number
3. Add notes if needed
4. Click **Save**

### Managing Parcel Companies

1. Navigate to **Parcel Companies** → **Create Company**
2. Enter company name
3. Optional: Add tracking URL template
4. Click **Save**

## Master Data Management

### Location Hierarchy

Manage the location hierarchy:
1. **Countries** → **States** → **Districts** → **Talukas**

For each level:
1. Navigate to the appropriate section
2. Click **Create**
3. Enter name and select parent (if applicable)
4. Click **Save**

### Educational Boards

1. Navigate to **Educational Boards** → **Create Board**
2. Enter board name
3. Click **Save**

### School Types

1. Navigate to **School Types** → **Create Type**
2. Enter type name
3. Click **Save**

### Student Types

1. Navigate to **Student Types** → **Create Type**
2. Enter type name
3. Click **Save**

### Semester Batches

1. Navigate to **Semester Batches** → **Create Batch**
2. Enter batch details:
   - Batch name
   - Start and end dates
   - Capacity
   - Status
3. Click **Save**

## Audit Logs

### Viewing Audit Logs

1. Navigate to **Audit Logs**
2. View all system activities:
   - User actions (create, update, delete)
   - Authentication events
   - Payment transactions
3. Filter by:
   - Date range
   - User
   - Action type
   - Entity type

## Search and Filtering

### Global Search

Use the search bar in the navigation to quickly find:
- Students (by name or email)
- Schools (by name)
- Users (by username or name)

### Advanced Filtering

Most list pages include filter panels with:
- Multi-select filters
- Date range filters
- Status filters
- Clear all filters option

## Best Practices

1. **Regular Backups**: Ensure database backups are configured
2. **User Management**: Regularly review user access and permissions
3. **Data Validation**: Always verify student data before bulk uploads
4. **Payment Verification**: Double-check payment details before recording
5. **Audit Trail**: Review audit logs regularly for security
6. **Master Data**: Keep master data (locations, types, boards) up to date

## Troubleshooting

### Cannot Login
- Verify username is correct
- Check if account is marked as inactive
- Try password reset if forgotten

### Cannot Delete Record
- Check if record is in use by other entities
- Predefined roles and system data cannot be deleted

### CSV Upload Fails
- Verify CSV format matches template
- Check for duplicate emails
- Ensure all required fields are filled
- Maximum 500 students per upload

### Invoice Not Generating
- Verify fee plan exists for the student's school/batch
- Check if student has required information
- Ensure semester batch is assigned

## Support

For technical support or questions:
- Contact your system administrator
- Review the FAQ document
- Check the technical documentation

## Keyboard Shortcuts

- **Ctrl/Cmd + K**: Open global search
- **Esc**: Close modals
- **Tab**: Navigate form fields

---

*Last Updated: November 2025*
*Version: 1.0*
