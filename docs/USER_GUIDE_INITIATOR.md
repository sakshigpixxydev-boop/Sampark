# Initiator User Guide - Pramukh Sankalp School Management System

## Introduction

Welcome to the Pramukh Sankalp School Management System. As an Initiator user, you can manage students and schools within your assigned scope. You have access to students and schools that have been specifically assigned to you.

## Getting Started

### Logging In

1. Navigate to the login page
2. Enter your username
3. Choose authentication method:
   - **Password**: Enter your password
   - **PIN**: Enter your 4-digit PIN
4. Click "Login"

### Dashboard Overview

After logging in, you'll see the Initiator Dashboard with:

- **Overview Cards**: Total students and schools assigned to you
- **Student Payments**: Pending payments for your students with school filters
- **New Inquiries**: Recent student registrations with payment status filters

**Important**: You can only view and manage students and schools that have been assigned to you by an administrator.

## Managing Your Students

### Adding Individual Students

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

You can upload multiple students at once using CSV files.

**Steps:**
1. Navigate to **Students** → **Bulk Upload**
2. Download the CSV template
3. Fill in student data following the template format
4. Select the Semester Batch
5. Upload the CSV file
6. Review the preview page:
   - Check calculated fees for each student
   - Review validation errors (if any)
   - Edit individual records if needed
7. Submit for accountant verification

**CSV Requirements:**
- Maximum 500 students per upload
- Required columns: student_type, education_board, standard, taluka, first_name, middle_name, last_name, email
- Email must be unique across all students
- All required fields must be filled

See the [Bulk Upload Guide](BULK_UPLOAD_GUIDE.md) for detailed instructions.

### Viewing and Filtering Students

1. Navigate to **Students**
2. Use filters to find specific students:
   - School
   - Payment Status
   - City
   - Standard
   - Educational Board
   - Student Type
3. Click on a student to view details

**Note**: You will only see students assigned to you.

### Editing Student Information

1. Navigate to **Students**
2. Click **Edit** on the student row
3. Update information
4. Click **Save**

## Managing Guardians

### Adding Guardians

1. Navigate to **Guardians** → **Create Guardian**
2. Fill in required fields:
   - Relation (Father, Mother, Guardian, etc.)
   - First Name, Middle Name, Last Name
3. Add contact information
4. Associate with student
5. Click **Save**

### Viewing Guardian Information

1. Navigate to **Guardians**
2. View list of all guardians for your students
3. Click on a guardian to see associated students

**Note**: Multiple guardians can be added per student.

## Managing Your Schools

### Viewing Schools

1. Navigate to **Schools**
2. View list of schools assigned to you
3. Click on a school to view details

**Note**: You can only view schools assigned to you by an administrator.

### School Information

For each school, you can view:
- School name and type
- Educational board
- Admin contact information
- Status (Lead or Final)
- Associated students

## Payment Tracking

### Viewing Pending Payments

1. Navigate to **Dashboard** → **Student Payments** section
2. View students with pending payments
3. Filter by school
4. Click on a student to view payment details

### Payment Status

- **Pending**: Payment not yet received
- **Authorized**: Payment approved but not completed
- **Paid**: Payment successfully completed
- **Failed**: Payment transaction failed

**Note**: As an Initiator, you can view payment status but cannot process payments. Contact an Accountant or Admin for payment processing.

## Invoice Management

### Viewing Invoices

1. Navigate to **Invoices**
2. View invoices for your students
3. Filter by:
   - Student
   - School
   - Status
   - Date range

### Invoice Status

- **DRAFT**: Not yet issued
- **ISSUED**: Sent to student/school
- **PARTIAL**: Partially paid
- **PAID**: Fully paid
- **VOID**: Cancelled

## Parcel Tracking

### Recording Parcels

1. Navigate to **Parcels** → **Create Parcel**
2. Select student (from your assigned students)
3. Select parcel company
4. Enter tracking number
5. Add notes if needed
6. Click **Save**

### Viewing Parcel Status

1. Navigate to **Parcels**
2. View all parcels for your students
3. Click tracking link to view shipment status

## Profile Management

### Updating Your Profile

1. Click on your name in the top right
2. Select **Profile**
3. Update your information
4. Click **Save**

### Changing Password

1. Go to **Profile** → **Change Password**
2. Enter current password
3. Enter new password
4. Confirm new password
5. Click **Update**

### Changing PIN

1. Go to **Profile** → **Change PIN**
2. Enter current PIN
3. Enter new 4-digit PIN
4. Confirm new PIN
5. Click **Update**

### Forgot Password/PIN

1. On the login page, click **Forgot Password** or **Forgot PIN**
2. Enter your email address
3. Check your email for reset instructions
4. Follow the link to reset your password/PIN

## Search Functionality

### Global Search

Use the search bar in the navigation to quickly find:
- Students (by name or email)
- Schools (by name)

**Note**: Search results are limited to your assigned students and schools.

### Advanced Filtering

Use filter panels on list pages to narrow down results:
- Apply multiple filters simultaneously
- Clear all filters to reset
- Filter results are saved during your session

## Notifications

### Email Notifications

You will receive email notifications for:
- Batch rejection (if your upload batch is rejected by accountant)
- Payment confirmations
- System announcements

### In-App Notifications

Watch for toast notifications for:
- Successful actions (green)
- Errors (red)
- Warnings (yellow)
- Information (blue)

## Best Practices

1. **Data Accuracy**: Always verify student information before submission
2. **Bulk Uploads**: Use CSV uploads for efficiency when adding multiple students
3. **Regular Updates**: Keep student and guardian information up to date
4. **Payment Tracking**: Monitor pending payments regularly
5. **Communication**: Maintain accurate contact information for students and guardians

## Common Tasks

### Adding a New Student

1. Collect all required student information
2. Navigate to **Students** → **Create Student**
3. Fill in all required fields
4. Add guardian information
5. Submit and verify student appears in your list

### Uploading Multiple Students

1. Prepare CSV file with student data
2. Navigate to **Students** → **Bulk Upload**
3. Select semester batch
4. Upload CSV
5. Review preview and fix any errors
6. Submit for verification
7. Wait for accountant approval

### Tracking Student Payments

1. Navigate to **Dashboard**
2. Check **Student Payments** section
3. Filter by school if needed
4. Contact students with pending payments
5. Coordinate with accountant for payment processing

### Sending Materials to Students

1. Ship materials to student
2. Navigate to **Parcels** → **Create Parcel**
3. Enter tracking information
4. Student can track shipment using provided link

## Troubleshooting

### Cannot See a Student
- Verify the student is assigned to you
- Contact administrator to assign student to your account

### Cannot See a School
- Verify the school is assigned to you
- Contact administrator to assign school to your account

### CSV Upload Fails
- Check CSV format matches template
- Verify no duplicate emails
- Ensure all required fields are filled
- Maximum 500 students per upload
- Check for validation errors in preview

### Batch Rejected by Accountant
- Check email for rejection reason
- Review and correct the issues
- Re-upload corrected data

## Limitations

As an Initiator user, you:
- Can only view and manage assigned students and schools
- Cannot process payments (Accountant role required)
- Cannot manage users or system configuration (Admin role required)
- Cannot approve upload batches (Accountant role required)

## Support

For assistance:
- Contact your administrator for access issues
- Contact accountant for payment questions
- Review the FAQ document
- Check the Bulk Upload Guide for CSV help

## Quick Reference

### Required Fields for Student Entry
- Student Type
- Educational Board
- Standard
- Taluka
- First Name, Middle Name, Last Name
- Email

### CSV Upload Limits
- Maximum: 500 students per upload
- File format: CSV only
- Email must be unique

### Payment Status Meanings
- **Pending**: Awaiting payment
- **Authorized**: Approved, processing
- **Paid**: Completed
- **Failed**: Transaction failed

---

*Last Updated: November 2025*
*Version: 1.0*
