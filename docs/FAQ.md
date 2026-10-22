# Frequently Asked Questions (FAQ)

## General Questions

### What is Pramukh Sankalp School Management System?

Pramukh Sankalp is a comprehensive web-based school management system designed to manage students, schools, payments, and administrative operations with role-based access control.

### Who can use the system?

The system supports four user roles:
- **Admin**: Full system access, manages all aspects
- **Initiator**: Manages assigned students and schools
- **School**: Manages students within their school
- **Accountant**: Verifies payments and processes upload batches

### What browsers are supported?

The system works best on:
- Google Chrome (recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari

Ensure your browser is up to date for the best experience.

### Is the system mobile-friendly?

Yes, the system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones

## Login and Authentication

### How do I log in?

1. Go to the login page
2. Enter your username
3. Choose to log in with password or PIN
4. Click "Login"

### What if I forget my password?

1. Click "Forgot Password" on the login page
2. Enter your email address
3. Check your email for reset instructions
4. Follow the link to create a new password

### What if I forget my PIN?

1. Click "Forgot PIN" on the login page
2. Enter your email address
3. Check your email for reset instructions
4. Follow the link to create a new PIN

### Can I use both password and PIN?

Yes, you can set both and use either one to log in. However, at least one must be set.

### Why can't I log in?

Common reasons:
- Incorrect username or password/PIN
- Account is marked as inactive
- Account has been locked
- Browser cookies are disabled

Contact your administrator if you continue to have issues.

### How do I change my password?

1. Log in to your account
2. Go to Profile → Change Password
3. Enter current password
4. Enter new password
5. Confirm new password
6. Click "Update"

### How do I change my PIN?

1. Log in to your account
2. Go to Profile → Change PIN
3. Enter current PIN
4. Enter new 4-digit PIN
5. Confirm new PIN
6. Click "Update"

## User Management

### How do I create a new user? (Admin only)

1. Navigate to Users → Create User
2. Fill in required fields (role, name, username)
3. Set password or PIN
4. Click "Save"

### Can I assign multiple schools to a user?

Yes, admins can assign multiple schools to Initiator users through the user assignment interface.

### Can I assign multiple students to a user?

Yes, admins can assign multiple students to Initiator users through the user assignment interface.

### What's the difference between inactive and deleted users?

- **Inactive**: User account exists but cannot log in; can be reactivated
- **Deleted**: User account is permanently removed (not recommended)

Use inactive status to temporarily disable accounts.

## Student Management

### How do I add a single student?

1. Navigate to Students → Create Student
2. Fill in all required fields
3. Add optional information
4. Click "Save"

### What information is required for a student?

Required fields:
- Student Type
- Educational Board
- Standard
- Taluka
- First Name, Middle Name, Last Name
- Email (must be unique)

### Can I add multiple students at once?

Yes, use the bulk upload feature with a CSV file. See the [CSV Upload Format](CSV_UPLOAD_FORMAT.md) guide.

### How many students can I upload at once?

Maximum 500 students per CSV upload. For more students, split into multiple files.

### What if a student's email is already in use?

Each student must have a unique email address. If an email is already in use, you'll need to use a different email.

### Can I edit student information after creation?

Yes, navigate to the student list, click Edit on the student row, make changes, and save.

### How do I add guardians for a student?

1. Navigate to Guardians → Create Guardian
2. Fill in guardian information
3. Associate with student
4. Click "Save"

Multiple guardians can be added per student.

## Bulk Upload

### Where do I get the CSV template?

Download the template from Students → Bulk Upload page, or refer to the [CSV Upload Format](CSV_UPLOAD_FORMAT.md) guide.

### What format should the CSV be in?

- File format: CSV (Comma-Separated Values)
- Encoding: UTF-8
- Required columns must be present
- See [CSV Upload Format](CSV_UPLOAD_FORMAT.md) for details

### Why is my CSV upload failing?

Common reasons:
- Incorrect file format (must be .csv)
- Missing required columns
- Duplicate emails
- Invalid data in fields
- More than 500 rows
- Master data doesn't exist (types, boards, locations)

Check the error messages for specific issues.

### How are fees calculated in bulk upload?

Fees are calculated based on:
1. Selected semester batch
2. Fee plan for that batch
3. School-level discounts (if applicable)
4. Student-level discounts (if applicable)

### Can I edit students after uploading CSV?

Yes, in the preview page you can edit individual student records before submitting for verification.

### What happens after I submit a bulk upload?

1. Batch is sent to accountant for verification
2. Accountant reviews student data and payment
3. Accountant approves or rejects the batch
4. If approved: Students are registered and can access system
5. If rejected: You receive email with reason and can correct and re-submit

### How long does accountant verification take?

This depends on your organization's workflow. Contact your accountant for expected turnaround time.

## Payment and Invoices

### How do I record a payment?

1. Navigate to Payments → Create Payment
2. Select student
3. Enter payment details (mode, date, transaction ID, amount)
4. Click "Save"

Note: Only Accountants and Admins can record payments.

### What payment modes are available?

Common payment modes:
- Cash
- Credit Card
- Debit Card
- Check
- Bank Transfer
- Online Payment

Contact admin if you need additional payment modes.

### How do I view a student's invoice?

1. Navigate to Invoices
2. Filter by student name or ID
3. Click on the invoice to view details

### What do the invoice statuses mean?

- **DRAFT**: Not yet finalized
- **ISSUED**: Sent to student/school
- **PARTIAL**: Partially paid
- **PAID**: Fully paid
- **VOID**: Cancelled

### How are discounts applied?

Discounts are applied automatically based on discount rules:
1. School-level discounts applied first
2. Student-level discounts applied second
3. Both FLAT (fixed amount) and PERCENT (percentage) types supported

### Can I generate an invoice manually?

Yes, navigate to Invoices → Create Invoice, select student, and the system will automatically calculate fees and discounts.

## Accountant Verification

### What is an upload batch?

An upload batch is a group of students uploaded together via CSV that are processed as a unit for payment verification.

### How do I approve an upload batch?

1. Navigate to Accountant → Upload Batches
2. Click on the batch to review
3. Verify all student data
4. Click "Approve Payment"
5. Enter payment details
6. Confirm approval

### How do I reject an upload batch?

1. Navigate to Accountant → Upload Batches
2. Click on the batch to review
3. Click "Reject Batch"
4. Enter detailed rejection reason
5. Confirm rejection

### What happens when I approve a batch?

- All students marked as paid
- Students converted to registered status
- Invoices generated
- Payment log created
- Uploader notified

### What happens when I reject a batch?

- Batch status changes to rejected
- Uploader receives email with reason
- Students remain in pending status
- Uploader can correct and re-submit

## School Management

### How do I add a new school?

1. Navigate to Schools → Create School
2. Fill in required fields (name, type, board, admin info)
3. Add optional information (logo, location)
4. Click "Save"

### What's the difference between Lead and Final status?

- **Lead (0)**: School is a potential client, not yet finalized
- **Final (1)**: School is confirmed and active

### Can I upload a school logo?

Yes, when creating or editing a school, you can upload a logo image file.

### How do I filter schools?

Use the filter panel to search by:
- School Type
- Educational Board
- Location
- Status

## Master Data

### What is master data?

Master data includes reference information used throughout the system:
- Countries, States, Districts, Talukas (locations)
- Educational Boards
- School Types
- Student Types
- Standards
- Payment Modes
- Parcel Companies

### Who can manage master data?

Only Admin users can create, edit, or delete master data.

### Why can't I delete a master data record?

Master data records cannot be deleted if they are in use by other records (e.g., a school type used by schools).

### How do I add a new location?

1. Navigate to Locations → [Country/State/District/Taluka]
2. Click Create
3. Enter name and select parent (if applicable)
4. Click "Save"

## Parcel Management

### How do I record a parcel shipment?

1. Navigate to Parcels → Create Parcel
2. Select student
3. Select parcel company
4. Enter tracking number
5. Add notes if needed
6. Click "Save"

### How do I track a parcel?

1. Navigate to Parcels
2. Find the parcel record
3. Click the tracking link (if parcel company has tracking URL)

### Can I add a new parcel company?

Yes (Admin only):
1. Navigate to Parcel Companies → Create Company
2. Enter company name
3. Add tracking URL template (optional)
4. Click "Save"

## Reports and Search

### How do I search for a student?

Use the global search bar in the navigation:
1. Click the search icon or press Ctrl/Cmd + K
2. Type student name or email
3. Select from results

### How do I filter student lists?

Use the filter panel on the student list page:
- School
- Assigned User
- Payment Status
- City
- Standard
- Educational Board
- Student Type

### Can I export data?

Export functionality depends on your role and system configuration. Contact your administrator for export options.

### How do I view audit logs? (Admin only)

1. Navigate to Audit Logs
2. View all system activities
3. Filter by date, user, action type, or entity

## Troubleshooting

### The page is loading slowly

Try:
- Refresh the page
- Clear browser cache
- Check internet connection
- Try a different browser
- Contact administrator if issue persists

### I'm getting a "Permission Denied" error

This means you don't have permission for that action. Contact your administrator to request access.

### Data is not saving

Check:
- All required fields are filled
- Data format is correct
- No validation errors shown
- Internet connection is stable
- Try again or contact administrator

### I can't see my students/schools

Check:
- Students/schools are assigned to you (Initiator role)
- You're logged into the correct account
- Filters are not hiding records
- Contact administrator to verify assignments

### CSV upload keeps failing

See the [CSV Upload Format](CSV_UPLOAD_FORMAT.md) guide for detailed troubleshooting.

Common issues:
- Incorrect file format
- Missing required columns
- Invalid data
- Duplicate emails
- Master data doesn't exist

## Security and Privacy

### Is my data secure?

Yes, the system implements multiple security measures:
- Password and PIN hashing
- CSRF protection
- Input validation and sanitization
- Role-based access control
- Audit logging
- Secure file uploads

### Who can see my data?

Access is role-based:
- **Admin**: Can see all data
- **Initiator**: Can see assigned students and schools
- **School**: Can see own school's students
- **Accountant**: Can see payment-related data

### How often should I change my password?

It's recommended to change your password every 90 days for security.

### What should I do if I suspect unauthorized access?

1. Change your password immediately
2. Contact your administrator
3. Review audit logs (if you have access)
4. Report any suspicious activity

## Technical Issues

### What if I encounter an error?

1. Note the error message
2. Try refreshing the page
3. Clear browser cache
4. Try a different browser
5. Contact administrator with error details

### How do I report a bug?

Contact your system administrator with:
- Description of the issue
- Steps to reproduce
- Screenshots (if applicable)
- Browser and device information

### Is there a mobile app?

The system is web-based and mobile-responsive. Access it through your mobile browser - no app installation needed.

## Getting Help

### Where can I find more documentation?

- [Admin User Guide](USER_GUIDE_ADMIN.md)
- [Initiator User Guide](USER_GUIDE_INITIATOR.md)
- [School User Guide](USER_GUIDE_SCHOOL.md)
- [Accountant User Guide](USER_GUIDE_ACCOUNTANT.md)
- [Bulk Upload Guide](BULK_UPLOAD_GUIDE.md)
- [CSV Upload Format](CSV_UPLOAD_FORMAT.md)

### Who do I contact for support?

- **Technical Issues**: System administrator
- **Access Issues**: System administrator
- **Payment Questions**: Accountant
- **Training**: Your organization's training coordinator

### Is training available?

Contact your organization about available training resources, including:
- User guides
- Video tutorials
- Live training sessions
- One-on-one support

---

*Last Updated: November 2025*
*Version: 1.0*
